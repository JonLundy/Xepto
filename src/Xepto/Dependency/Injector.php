<?php namespace Xepto\Dependency;

//!-- [ Trait: DependancyInjector ] --------

trait Injector
{
    protected $config;
    protected $request;
    protected $response;
    protected $persist;
    protected $token;

    public function __construct()
     {
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
                $this->$name = $di;
                break;
            }
         }
         if (method_exists($this,'init')) $this->init();
     }
}
