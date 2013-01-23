<?php namespace XeptoAuthToken;

use Xepto;
use XeptoAuthToken;

chdir($_SERVER['DOCUMENT_ROOT']);
set_include_path(get_include_path().':lib/');

require 'Xepto/include/get_headers.php';
require 'Xepto/include/get_query.php';
require 'Xepto/include/get_autoloader.php';

$request  = new Xepto\Request\Request  ();

$env = $request->server('APP_ENV');

$config = new Xepto\Config\Config();
$config->merge(require "XeptoCheck/config/check.$env.php");

$response = new Xepto\Request\Response ($config->response, $request);

$persist    = new XeptoAuthToken\Token\Persist   ($config->persist);
$encryption = new XeptoAuthToken\Token\Encryption(MCRYPT_BlOWFISH, MCRYPT_MODE_CBC);
$token      = new XeptoAuthToken\Token\Token     ($config->token, $request, $response, $persist, $encryption);

$cors       = new XeptoAuthToken\App\CORS        ($config->cors,  $request, $response);
$rules      = new XeptoAuthToken\App\Rules       ($config->rules, $request, $response, $persist, $token);

if ($cors->doPreflight())   return $response->allow();
if (!$rules->checkLimits()) return $response->deny(403);
if (!$rules->checkRules())  return $response->deny(403);

$time = (microtime(true) - $request->server('REQUEST_TIME_FLOAT')) * 1000;
$response->set(['X-HRIT-Debug' => $time]);

return $response->allow();