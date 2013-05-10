<?php namespace Xepto\Request;

use Xepto;

class Router
{
    use Xepto\Dependency\Injector;
    private $__inject = ['request','response'];

    public function init()
     {
        $this->routes         = $this->config['routes'];
        $this->regex_routes   = $this->config['regex_routes'];
     }

    public function run($config)
     {
        $route        = $this->request->server('DOCUMENT_URI');
        $route_method = strtolower($this->request->server('REQUEST_METHOD'));
 
        $route_param = [];

        if (array_key_exists($route, $this->routes))
           $route_class = $this->routes[$route];

        else if (count($this->regex_routes) > 0) {
            $ucf = function ($word) { return ucfirst(strtolower($word)); };
            $cml = function ($word) { return ucfirst($word); };
            $tpl = function ($word) { return '/\{' . $word . '\}/'; };

            foreach ($this->regex_routes as $regex => $opts) {
                if (preg_match($regex, $route, $match)) {
                    $match['method'] = $route_method;
                    $m = array_replace($opts, $match);

                    $route_param = $m;
                    
                    if (array_key_exists('map', $opts)) {
                        $map = $opts['map'];
                        
                        if (strpos($map,'::') === false) 
                            $map .= '::{method}'; 
                        
                        list($class, $method) = explode('::',$map,2);
                        
                        $route_class   = preg_replace(array_map($tpl, array_keys($m)), 
                                                      array_map($cml, array_values($m)), $class);
                        $route_method  = preg_replace(array_map($tpl, array_keys($m)), array_values($m), $method);
                    }    
                    else $route_class = implode('\\', [$m['ns'], $ucf($m['module']), $ucf($m['class']) ]);
                }    
            } 
        }

        if ($route_class === null) return $this->response->deny(404);

        if (class_exists($route_class, true)) {
            $app = new $route_class($config); 
        } else {
            return $this->response->deny(405);
        }
        
        $this->request->set('path',null,$route_param);
        
        if (!method_exists($app, $route_method)) return $this->response->deny(405);
        return $app->$route_method();
     }
}








