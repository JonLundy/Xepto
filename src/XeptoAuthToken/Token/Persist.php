<?php namespace XeptoAuthToken\Token;

use Xepto;

class Persist
 {
    use Xepto\Dependency\Injector;

    public function init()
     {
        $this->__injector($this->config['db']);
     }

    public function incrCounter ($prefix, $name, $limit, $timeout)
     {
        $count = (int) $this->redis->get($prefix.$name);
        if ($count > $limit) {
           return false;
        } elseif ($count == 0) {
            $this->redis->multi();
            $this->redis->incr($prefix.$name);
            $this->redis->expire($prefix.$name, $timeout);
            $this->redis->exec();
        } else {
            $this->redis->incr($prefix.$name);
        }

        return $count;
     }
    public function get($key)
     {
         return $this->redis->get($key);
     }
    public function set($key, $value)
     {
         return $this->redis->set($key, $value);
     }
    public function setex($key, $value, $expire)
     {
         return $this->redis->setex($key, $expire, $value);
     }

 }
