<?php 

//----[BootStrap]--- 

use Xepto;

chdir($_SERVER['DOCUMENT_ROOT']);
set_include_path(get_include_path().':lib/');

require 'Xepto/include/get_headers.php';
require 'Xepto/include/get_query.php';
require 'Xepto/include/get_post.php';
require 'Xepto/include/get_autoloader.php';

$config  = new Xepto\Config\Config();
$request = $config->buildClass('request','\Xepto\Request\Request'); 

//----[BootStrap]--- 

$env = $request->server('APP_ENV');

$config->merge(require "config/XeptoAuthToken.$env.php");
$config->merge(require "config/XeptoAuthCAS.$env.php");

$router   = $config->getClass('router'); 

return $router->run($config->app);