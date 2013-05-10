<?php namespace XeptoAuthCAS\App;

use Xepto;

set_include_path(get_include_path().':vendor/phpCAS/source/');
require_once 'CAS.php';

class Auth
{
    use Xepto\Dependency\Injector;
    private $__inject = ['request', 'response', 'token'];

    public function get_home()
     {
        echo '<pre>'.yaml_emit($this->token->getIdent()).'</pre>';

        echo '<a href="/auth.login">Login</a><br/>';
        echo '<a href="/auth.logout">Logout</a><br/>';

        echo $this->config->cas['ca'];
     }

    public function get_token ()
     {
        $this->response->set(['Content-Type' => 'text/plain']);

         if ($this->request->get('access_token', null) !== null) {
             $token_str = $this->request->get('access_token');
             $token = $this->token->loadToken($token_str);

             echo yaml_emit($token);
             die();
         }

         $client  = $this->request->get('client','*');
         $aspect  = $this->request->get('aspect','*');
         $ident   = $this->request->get('ident', '*');
         $expires = $this->request->get('expires', time() + 162000);

         $expires = $expires == 'N' ? null : (int) $expires;

        $params = [
            'u' => $ident,
            'a' => $aspect,
            'c' => $client,
            'e' => $expires
        ];
        $token = $this->token->loadIdent($params);

        echo "?access_token=$token\n\n";
        echo yaml_emit($token->getParams());
     }
     
    public function get_login()
     {
        $config = $this->config;
        $callback = null;
        $callback = $this->request->header('Referer',$callback);
        $callback = urldecode($this->request->get('cb',$callback));

        if ($callback !== null) {
            $callback = str_replace('http://','https://',$callback);

            $allow = false;

            foreach ( $config->allow->toArray() as $compare) {
                if (strncmp($compare, $callback, strlen($compare))) $allow = true;
            }

            $token = $this->request->get('t','');

            if ($allow === false) {
                $this->response->set(['Location' => $config['default']]);
                 die();
            }
        } else $callback = $config['default'];

        $handler = $config['handler'];

        $cb = urlencode($callback);

        $this->response->set(['Location' => "$handler?cb=$cb"]);
     }

    public function get_logout ()
     {
        $cas = $this->config->cas;

        $cas_host   = $cas->host;
        $cas_port   = $cas->port;
        $cas_ctx    = $cas->ctx;
        $cas_ca     = $cas->ca;
        $cas_verify = $cas->verify;
        $allow_list = $this->config['allow'];
        
        $referer = $this->request->header('Referer', null);
        if ($referer !== null) {
            $allow = false;

            $callback = str_replace('http://','https://',$referer);

            foreach ( $allow_list as $compare) {
                if (strncmp($compare, $callback, strlen($compare))) $allow = true;
            }

            if ($allow === false) {
                 header('HTTP/1.1 403 Forbidden');
                 die();
            }

        } else $callback = $config->default;

        $this->token->setCookie(false);

        \phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_ctx, false);

        if ($cas_verify)
             \phpCAS::setCasServerCACert($cas_ca, false);
        else \phpCAS::setNoCasServerValidation();

        \phpCAS::logoutWithRedirectService($callback);
     }
     
    public function get_ticket()
     {
        $cas = $this->config->cas;

        $cas_host   = $cas->host;
        $cas_port   = $cas->port;
        $cas_ctx    = $cas->ctx;
        $cas_ca     = $cas->ca;
        $cas_verify = $cas->verify; 
        $allow_list = $this->config['allow'];

        $renew = $this->request->get('renew', false);

        $ticket = $this->token->getIdent()[2];
        $ticket = $this->request->get('ticket', $ticket);

        \phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_ctx, false);

        \phpCAS::setNoClearTicketsFromUrl();

        if ($cas_verify)    
             \phpCAS::setCasServerCACert($cas_ca, false);
        else \phpCAS::setNoCasServerValidation();

        if (!$renew && \phpCAS::isAuthenticated()) {
            $params = array(
                'u'  => (int) substr(\phpCAS::getUser(), 1),
                'a' => '*',
                'c' => '*',
                'e'=> time() + 162000,
                't' => $ticket
            );
            $this->token->loadIdent($params);
            $this->token->setCookie(true);

            $cb = $this->request->get('cb', false);
            if ($cb) {
                $callback = str_replace('http://', 'https://', urldecode($cb));

                $allow = false;

                foreach ($allow_list as $compare) {
                    if (strncmp($compare, $callback, strlen($compare))) $allow = true;
                }

                if ($allow === false) {
                     return $this->response->deny(403);
                }
            } else $callback = $this->config['default'];

            return $this->response->set(['Location' => $callback]);
        }
        
        $this->token->setCookie(false);

        \phpCAS::setServerLoginURL(\phpCAS::getServerLoginURL().'&renew=1');
        \phpCAS::forceAuthentication();
     }

    public function post_ticket()
     {
        $xml = $this->request->post('logoutRequest');
        $xml = simplexml_load_string ($xml);
        $ticket = $xml->children('samlp', true)->SessionIndex;
        $this->token->revokeTicket($ticket);
        
        error_log('Signout Detected: '. $ticket);
     }
}
