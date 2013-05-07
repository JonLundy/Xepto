<?php namespace Xepto\Request;

use Xepto\Dependency as Dependency;

class Router
{
    use Dependency\Injector;
    private $__inject = ['request','response'];

    public function init()
     {
        $this->routes   = $this->config['routes'];
     }

    public function run($config)
     {
        $route  = $this->request->server('DOCUMENT_URI');
        $method = strtolower($this->request->server('REQUEST_METHOD'));

        $route_class = $this->routes[$route];

        if ($route_class === null) return $this->response->deny(404);

        $app = new $route_class($config); 

        if (!method_exists($app, $method)) return $this->response->deny(405);
        return $app->$method();
     }
}
