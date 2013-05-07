<?php namespace XeptoAuthCAS\App;

use Xepto;

class Login
{
    use Xepto\Dependency\Injector;
    private $__inject = ['request','response'];

    public function get()
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
                $this->response->set(['Location' => $config->default]);
                 die();
            }
        } else $callback = $config->default;

        $handler = $config->handler;

        $cb = urlencode($callback);

        $this->response->set(['Location' => "$handler?cb=$cb"]);
     }
}
