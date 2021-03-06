<?php
class Model
{
    public static function load($name)
    {
        $filename = ROOT.'models/'.$name.".php";
        if (file_exists($filename)) {
            require_once($filename);
            //	return new $name();
        } else {
            echo "Class not found !";
        }
    }

    // loadConfig permet de charger tout ou partie du fichier de configuration d�fini par d�faut dans la constante CONFIG_FILE

    public static function loadConfig($section = null, $f = CONFIG_FILE)
    {
        $config = parse_ini_file($f, true); // Retourne la config en prenant compte des sections sous forme d'un array

        if (empty($section)) { return $config; } else { return $config[$section]; }
    }
}
