<?php

define('ROOT', str_replace('mmshandler.php', '', $_SERVER['SCRIPT_FILENAME']));
define('CONFIG_FILE', ROOT.'config.ini');

require_once(ROOT.'models/Model.php');

?>