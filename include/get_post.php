<?php namespace Xepto;

$_POST = [];   
$_RAWPOST = [];

if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
    $_RAWPOST = file_get_contents('php://input');

    $c_type = explode('; ',$_SERVER['HTTP_CONTENT_TYPE']); 
    switch ($c_type[0]) {  
    case 'application/json': 
        $_POST = json_decode($_RAWPOST,true); 
        break;
    case 'text/yaml': 
        $_POST = yaml_parse($_RAWPOST); 
        break;
    case 'multipart/form-data': 
        $boundary = null;
        $files = [];
        $items = [];
        foreach(explode(PHP_EOL, $_RAWPOST, 2) as $n) {
            if ($boundary === null) $boundary = trim($n);
        }        
        foreach (explode($boundary, $_RAWPOST) as $n) {
            if (trim($n) == '') continue;
            if (trim($n) == '--') break;

            $name = null;
            $item = [];
            $trim = false;
            foreach(explode(PHP_EOL, $n, 4) as $h) {
                $h = trim($h);
                if ($h == '') continue;
                if ($h == $boundary) continue; 
                
                if (substr_compare('Content-Disposition', $h, 0, 19) == 0) {
                    foreach(explode('; ', $h) as $i) { 
                        $j = explode('"',$i); 
                        if (substr_compare('name=',$j[0],0) == 0) $name = trim($j[1]);
                        elseif (substr_compare('filename=',$j[0],0) == 0) $item['filename'] = trim($j[1]);
                    }
                }    
                elseif (substr_compare('Content-Type', $h, 0, 12) == 0) { 
                    $item['type'] = trim(explode(' ', $h)[1]); $trim = true; 
                }
                                
                if ($trim) $item['data'] = substr($h,2,-2);
                else $item['data'] = trim($h);
            }
            if ($name !== null)
                if (count($item) == 1) {
                     array_push($items, implode('=', [$name, $item['data']]));
                } else {
                    $item['data'] = urldecode($item['data']);
                    $item['filename'] = urldecode($item['filename']);
                    $item['type'] = urldecode($item['type']);
                    $name = urldecode($name);
                    $files[$name][] = $item;
                }    
        }   
        if (count($items)) {
            parse_str(implode('&',$items), $params);
            $_POST = array_merge_recursive($files, $params);
        }    
        break;
    default: 
        parse_str($_RAWPOST, $_POST);
    }
}


