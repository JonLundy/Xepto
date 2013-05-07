<?php namespace Xepto\Dependency;

trait Injector
{
    private $__classes = [];

    public function __construct()
     {
        $this->__classes = [];
        $items = func_get_args();

        foreach ($items as $di) {
            $type = gettype($di) == 'object' ? get_class($di) : 'array';

            switch ($type) {
             case 'array':
                foreach ($di as $k => $v) {
                    $this->$k = $v;
                }
            default:
                $ex = explode('\\', $type);
                $name = strtolower($ex[count($ex) - 1]);
                $this->__classes[$name] = $di;
                break;
            }
         }

         if (isset($this->__inject))
             foreach ($this->__inject as $name) $this->__injector($name);
             
         if (method_exists($this,'init')) $this->init();
     }
    public function __injector($name)
     {
        // if it was injected on instantiation use that. 
        if (array_key_exists($name, $this->__classes)) {
            return;
        }
        // second check the config registry.
        $class = $this->config->getClass($name);
        if ($class !== false) {
            $this->__classes[$name] = $class;
            return;
        }

        // write notice to error log. 
        $trace = debug_backtrace();
        trigger_error(
            'Undefined class via injector: ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);   
        die;         
     }
    public function __get($name)
     {
        if (array_key_exists($name, $this->__classes)) {
            return $this->__classes[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
     }      
}
