<?php namespace Xepto\Config;

class Config implements \ArrayAccess
 {
    protected $store;
    protected $classes;
    public $root;

    public function __construct($config = null, $root = null)
     {
        $this->root = $root === null ? $this : $root;
        $this->store = [];
        $this->classes = [];
        if ($config !== null) $this->merge($config);
     }

    public function merge($config)
     {
        $this->store = array_replace_recursive($this->store, $config);
     }

    public function toArray()
     {
         return $this->store;
     }

    public function __get($name)
     {
         if (is_array($this->store)) {
             if (array_key_exists($name,$this->store)) {
                if (is_array($this->store[$name]))
                     return new Config($this->store[$name], $this->root);
                else return $this->store[$name];
             }
         } else return $this->store;

         return null;
     }

    // ArrayAccess Items
    public function offsetSet($offset, $value)
     {
        // Config is Read-Only.
     }

    public function offsetExists($offset)
     {
        return array_key_exists($offset,$this->store);
     }

    public function offsetUnset($offset)
     {
        // Config is Read-Only.
     }

    public function offsetGet($offset)
     {
        return $this->__get($offset);
     }
     
    // Used for class object injection. 
    public function storeClass($name, $class) 
     {
         $this->root->classes[$name] = $class;
     } 
    public function getClass($name) 
     {
        if (array_key_exists($name, $this->root->classes))
            return $this->root->classes[$name]; 
         
        if (!array_key_exists($name, $this->root->store)) 
            return false;
             
        $class = $this->buildClass($name);
        
        if ($class === false) 
            return false;
                     
        return $class;             
     } 
    public function buildClass($name, $className = null)
     {
        if ($className !== null) {
            $config = $this->root[$name];
            if ($config === null)  
                $class = new $className ();
            else
                $class = new $className ($config);
        } else {
            $config = $this->root[$name];
            $className = $config->__class;
            
            if ($className === null) 
                return false;
    
            if ($config->__configArray === true)
                $class = new $className ($config->toArray());
            else
                $class = new $className ($config);
        }        
            
        $this->storeClass($name, $class);
        
        return $class;
     } 
 }
