<?php
require_once('core.php');

define('CONFIG_FILE', ROOT.'config.ini');

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