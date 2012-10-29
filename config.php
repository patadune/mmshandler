<!DOCTYPE html>

<html lang="fr">
<head>
  <meta charset="UTF-8">

  <title>Générateur de config.ini</title>
</head>
<body>

<?php

session_start();
require_once('models/TumblrOAuth.php');

if(empty($_POST) && empty($_GET)):

?>

<form action="config.php" method="post">
	<input type="text" name="consumer_key"> Clé consumer<br />
	<input type="text" name="consumer_secret"> Clé secrète consumer<br />
	<input type="submit" value="Envoyer">

<?php

elseif(!empty($_POST)):

$callback_url = "http://re3m1.no-ip.org/dev.mmshandler/config.php";

$_SESSION = $_POST;
$tum_oauth = new TumblrOAuth($_SESSION);
$request_token = $tum_oauth->getRequestToken($callback_url);
$_SESSION['request_token'] = $token = $request_token['oauth_token'];
$_SESSION['request_token_secret'] = $request_token['oauth_token_secret'];

switch ($tum_oauth->http_code) {
  case 200:
  
    $url = $tum_oauth->getAuthorizeURL($token);
	
    header('Location: ' . $url);
	
    break;
  default:
  
	echo "Une erreur est survenue.";
	break;
}

else:

$tum_oauth = new TumblrOAuth($_SESSION);
$access_token = $tum_oauth->getAccessToken($_GET['oauth_verifier']);
unset($_SESSION['request_token']);
unset($_SESSION['request_token_secret']);

if (200 == $tum_oauth->http_code) {

} else {
  die("Impossible de s'identifier.");
}

$_SESSION['access_token'] = $access_token['oauth_token'];
$_SESSION['access_token_secret'] = $access_token['oauth_token_secret'];

// Récupère le nom du blog

$tum_oauth = new TumblrOAuth($_SESSION);

$userinfo = $tum_oauth->get('http://api.tumblr.com/v2/user/info');

if (200 == $tum_oauth->http_code) {
  // good to go
} else {
  die('Unable to get info');
}

$_SESSION['blog_name'] = $userinfo->response->user->name.".tumblr.com";

?>
<p>A copier dans le fichier config.ini :</p>
<pre>
[oauth]<br />
<?php
foreach($_SESSION as $k => $v) {
	echo $k.' = "'.$v.'"<br />';
}

endif;


?>

</body>
</html>