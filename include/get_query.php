<?php namespace Xepto;

{  // Process the Get header
    $ex = explode('?',$_SERVER['REQUEST_URI']);
    if (count($ex) > 1) parse_str($ex[1],$_GET);
}
