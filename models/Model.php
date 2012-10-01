<?php

class Model {

    static function load($name){
        
        $filename = ROOT.'models/'.$name.".php";
        if(file_exists($filename)) {
            require($filename);
            // return new $name();
        } else {
            echo "Class not found !";
        }
    }
    
}