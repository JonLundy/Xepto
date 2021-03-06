<?php namespace Xepto\Auth\Token;

use Xepto;

class CORS
{
  use Xepto\Dependency\Injector;
  private $__inject = ['request','response'];

  protected $allowed;
  protected $max_age;
  protected $methods;

  public function init()
  {
      $config = $this->config;
      $this->max_age = $config['max_age'] ? $config['max_age'] : null;
      $this->allowed = $config['allowed'] ? $config['allowed'] : array();
      $this->methods = $config['methods'] ? $config['methods'] : array();
   }

  public function getHeaders()
   {
     $origin = strtolower($this->request->server('HTTP_ORIGIN'));
     $method = $this->request->server('HTTP_ACCESS_CONTROL_REQUEST_METHOD');
     $custom = $this->request->server('HTTP_ACCESS_CONTROL_REQUEST_HEADERS',null);

     if (in_array($origin, $this->allowed)) {
         return [
             'Access-Control-Allow-Origin' => $origin,
             'Access-Control-Allow-Methods' => in_array($method, $this->methods) ? implode(',',$this->methods) : null,
             'Access-Control-Allow-Headers'=> $custom,
             'Access-Control-Max-Age' => $this->max_age,
             'Access-Control-Allow-Credentials' => 'true',
          ];
      }
   }

  public function doPreflight()
   {
      $this->response->set($this->getHeaders());

      if ($this->request->server('REQUEST_METHOD') == 'OPTIONS') {
        return true;
      }

      return false;
   }
}
