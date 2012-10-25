<?php
class Model {

    static function load($name){
        
        $filename = ROOT.'models/'.$name.".php";
        if(file_exists($filename)) {
            require_once($filename);
            //	return new $name();
        } else {
            echo "Class not found !";
        }
    }
	
	// loadConfig permet de charger tout ou partie du fichier de configuration défini par défaut dans la constante CONFIG_FILE
	
	static function loadConfig($section = null, $f = CONFIG_FILE) {
		
		$config = parse_ini_file($f, true);
		if(empty($section)) { return $config; } 
		else { return $config[$section]; }
	}
}
?>