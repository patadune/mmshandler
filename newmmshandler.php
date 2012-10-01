<pre>
<?php
require_once('core.php');

$mp = Model::load('MailProcessor');
$mp->getStructures();
?>