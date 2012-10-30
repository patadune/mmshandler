<?php

define('ROOT', str_replace('mmshandler.php', '', $_SERVER['SCRIPT_FILENAME']));
define('CONFIG_FILE', ROOT.'config.ini');

require_once(ROOT.'models/Model.php');

Model::load('MailProcessor');
$mp = new MailProcessor();
//$mp->debugMode(true);
$mp->fetchStructures();

foreach($mp->getStructures() as $msgNo => $structure) { //	Boucle principale, une itération par mail à traiter
	
	$mp->processMailParts($msgNo, $structure);
	$mp->definePostType();
	$mp->MailToPost();
	$mp->setTweet("off");
	$mp->sendPost();
	$mp->clearVars();
}
?>