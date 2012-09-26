<?php

//	Since I needed only an permanent access for my script, which tend to be fully automatic, I modified the script from lucasec (https://groups.google.com/forum/?fromgroups=#!topic/tumblr-api/g6SeIBWvsnE) to avoid regeneration of tokens and user inputs.


// Load the library

require_once('tumblroauth/tumblroauth.php');

// Define the needed keys
require_once('config.php');

// Start a new instance of TumblrOAuth, overwriting the old one.
// This time it will need our Access Token and Secret instead of our Request Token and Secret
$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);

// You don't actuall have to pass a full URL,  TukmblrOAuth will complete the URL for you.
// This will also work: $userinfo = $tum_oauth->get('user/info');

$url = "http://api.tumblr.com/v2/blog/BLOGNAME/post";
$parameters = Array(
						'type' => 'text',
						'title' => 'Test.',
						'body' => 'This is a test from Tumblr API');

$blog_post = $tum_oauth->post($url, $parameters);

if (201 == $tum_oauth->http_code) {
	echo "Post created !";
} else {
  die('Unable to post : '. $tum_oauth->http_code);
}
// And that's that.  Hopefully it will help.
?>
