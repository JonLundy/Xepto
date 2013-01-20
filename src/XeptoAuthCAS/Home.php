<?php namespace XeptoAuthCAS;

use Xepto;

class Home
{
    use Xepto\DependencyInjector;

    public function get()
     {
        echo '<pre>'.yaml_emit($this->token->getIdent()).'</pre>';

        echo '<a href="/auth.login">Login</a><br/>';
        echo '<a href="/auth.logout">Logout</a><br/>';

     }

    public function post()
     {

         echo $this->request->raw();

     }
}
