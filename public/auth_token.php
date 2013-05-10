<?php

//----[BootStrap]--- 

use Xepto;

chdir($_SERVER['DOCUMENT_ROOT']);
set_include_path(get_include_path().':lib/');

require 'Xepto/include/get_headers.php';
require 'Xepto/include/get_query.php';
// require 'Xepto/include/get_post.php'; // Disabled to stop request hanging issue.
require 'Xepto/include/get_autoloader.php';

$config  = new Xepto\Config\Config();
$request = $config->buildClass('request','\Xepto\Request\Request'); 

//----[BootStrap]--- 

$env = $request->server('APP_ENV');

$config->merge(require "config/XeptoAuthToken.$env.php");

$cors     = $config->getClass('cors');     
$rules    = $config->getClass('rules');    
$response = $config->getClass('response'); 

if ( $cors->doPreflight() ) return $response->allow();
if (!$rules->checkLimits()) return $response->deny(403);
if (!$rules->checkRules() ) return $response->deny(403);

// $time = (microtime(true) - $request->server('REQUEST_TIME_FLOAT')) * 1000;
// $response->set(['X-HRIT-Debug' => $time]);

return $response->allow();