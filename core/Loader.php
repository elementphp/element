<?php

namespace element\core;

require('Config.php');

spl_autoload_register(function ($class){
    
    // files/classes to ignore in autoload
    $ignores = [
        "Smarty"
    ];

    foreach($ignores as $ignore) {
        if(strpos($class, $ignore) !== false ) {
            return;
        }
    }
   

    $found = false;

    $prefixes = array(
            "element\\mvc\\",
            "element\\core\\",
            "element\\db\\"
    );

    $class = str_replace($prefixes, "", $class);

    $relative = str_replace(basename(__DIR__),"",__DIR__);

    $modelPath = $relative   . "app/models";

    $paths = [
        __DIR__     . "/",
        __DIR__     . "/settings"
    ];

    $commonpaths = [
        $relative   . "app/controllers",
        $relative   . "app/helpers"
    ];

    $controllerSuffix = Config::get("defaults","controller_suffix");
    
    $knownSuffixes = [
        $controllerSuffix,
        "Helper"
    ];

    if(strpos($class, $knownSuffixes[0]) > 0) {
        $file =  $commonpaths[0] . '/' . $class . '.php'; 
        $file = str_replace('\\','/',$file);
        $file = str_replace('//','/',$file);

        if(file_exists($file)) {
            require $file;
            $found = true;
        }
    }

    if(strpos($class, $knownSuffixes[1]) > 0) {
        $file =  $commonpaths[1] . '/' . $class . '.php'; 
        $file = str_replace('\\','/',$file);
        $file = str_replace('//','/',$file);

        if(file_exists($file)) {
            require $file;
            $found = true;
        }
    }
    
    if(!$found) {
        $file =  $modelPath . '/' . $class . '.php'; 
        $file = str_replace('\\','/',$file);
        $file = str_replace('//','/',$file);

        if(file_exists($file)) {
            require $file;
            $found = true;
        }
    }
    
    if(!$found) {
        foreach($paths as $path){
            $file =  $path . '/' . $class . '.php'; 
            $file = str_replace('\\','/',$file);
            $file = str_replace('//','/',$file);

            if(file_exists($file)) {
                $found = true;
                require $file;
                break;
            }
        }
    }

    if(!$found) {
        if(Config::get("errors","redirect")) {
            $location = Config::get("errors", "redirect_location");
            $r = new Response();
            $r->redirect($location);
        }
    }
});