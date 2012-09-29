#!/usr/bin/php

<?php

require_once('core.php');

$imap = Imap::open();

//	R�cup�re le nombre de messages sur la bo�te mail

$msgNbr = imap_num_msg($imap);
$msgProcessed = 0;

//	R�cup�re la structure des mails dont l'exp�diteur est MAILSENDER (soit un MMS (en th�orie))

for($i = 0; $i < $msgNbr; $i++) {
	
	$headers = imap_headerinfo($imap, $i + 1);
	$sender = $headers->senderaddress;
	$readStatus = $headers->Unseen;
	if($sender == "MAILSENDER" && $readStatus == "U") {
		$structures[$i + 1] = imap_fetchstructure($imap, $i + 1);
	}
}

//	
if(empty($structures)) {
	die("No new MMS !");
}

foreach($structures as $msgNo => $structure) {
	
	//	echo "<h3>Mail n�" . $msgNo . " !</h3>";
	//	print_r(imap_headerinfo($imap, $msgNo));
	$headers = imap_headerinfo($imap, $msgNo);
	if(isset($headers->subject)) {
		$subject = $headers->subject;
	}
	
	//	$post_parameters['date'] = $headers->date;
	//	$post_parameters['date'] = strtotime($headers->date);
	//	$post_parameters['date'] = date("Y-m-d H:i:s", strtotime($headers->date));
	
	foreach($structure->parts as $partNo => $part) {
		
 		switch($part->type) {
		
			case 0:	//	Type 0 : text
				if($part->parameters[1]->value != "banniere.txt") { //	Supprime l'ent�te d'Orange
					//	echo $part->parameters[1]->value . "<br />";
					
					$data[$partNo]['type'] = 'text';
					$data[$partNo]['data'] = base64_decode(imap_fetchbody($imap, $msgNo, $partNo + 1, Imap::fetch_options));
					
					// $file = fopen("/home/remi/www/mails/".$part->parameters[1]->value, "w");
					// fwrite($file, $data[$partNo]['data']);
					// fclose($file);
				}
				break;				
				
			//	Type 1 : multipart
								
			//	Type 2 : message
								
			//	Type 3 : application
								
			case 4:	//	Type 4 : audio
				//	echo $part->parameters[0]->value ."<br />";
				
				$post_parameters['type'] = $data[$partNo]['type'] = 'audio';
				$data[$partNo]['data'] = base64_decode(imap_fetchbody($imap, $msgNo, $partNo + 1, $fetch_options));
				
				// $file = fopen("/home/remi/www/mails/".$part->parameters[0]->value, "w");
				// fwrite($file, $data[$partNo]['data']);
				// fclose($file);
				break;
								
			case 5:	//	Type 5 : image
				if($part->parameters[0]->value != "logo.gif") {	//	Enl�ve le logo d'Orange
					//	echo $part->parameters[0]->value ."<br />";
					
					$post_parameters['type'] = $data[$partNo]['type'] = 'photo';
					$data[$partNo]['data'] = base64_decode(imap_fetchbody($imap, $msgNo, $partNo + 1, $fetch_options));
				
					// $file = fopen("/home/remi/www/mails/".$part->parameters[0]->value, "w");
					// fwrite($file, $data[$partNo]['data']);
					// fclose($file);
				}
				break;
								
			case 6:	//	Type 6 : video
				//	echo $part->parameters[0]->value ."<br />";
				
				$post_parameters['type'] = $data[$partNo]['type'] = 'video';
				$data[$partNo]['data'] = base64_decode(imap_fetchbody($imap, $msgNo, $partNo + 1, $fetch_options));
				
				// $file = fopen("/home/remi/www/mails/".$part->parameters[0]->value, "w");
				// fwrite($file, $data[$partNo]['data']);
				// fclose($file);
				break;
								
			//	Type 7 : other
		}
	}
	
	if(!isset($post_parameters['type'])) {
		$post_parameters['type'] = "text";
	}
	
	if(isset($subject) && $post_parameters['type'] == "text") {
		$post_parameters['title'] = $subject;
	}
	// A ce stade, on a toutes les donn�es qui nous int�ressent dans $data, et le type de post dans $post_parameters['type']
	
	foreach($data as $pdata) {
		switch($pdata['type']) {
			
			case "text":
				if($post_parameters['type'] == "text") {
					if(!empty($post_parameters['body'])) {
						$post_parameters['body'] = $post_parameters['body'] . "<br />" . $pdata['data'];	// On rajoute � la suite
					}
					else {
						$post_parameters['body'] = $pdata['data'];
					}
				}
				else {
					if(!empty($post_parameters['caption'])) {
						$post_parameters['caption'] = $post_parameters['caption'] . "<br />" . $pdata['data'];	// On rajoute � la suite
					}
					else {
						$post_parameters['caption'] = $pdata['data'];
					}
				}
				break;
			
			case "photo":
				$post_parameters['data'] = $pdata['data'];
				break;
				
			case "audio":
				$post_parameters['data'] = $pdata['data'];
				break;
				
			case "video":
				$post_parameters['data'] = $pdata['data'];
				break;
		}
	}

	// Start a new instance of TumblrOAuth, overwriting the old one.
	// This time it will need our Access Token and Secret instead of our Request Token and Secret
	$tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);

	// You don't actuall have to pass a full URL,  TukmblrOAuth will complete the URL for you.
	// This will also work: $userinfo = $tum_oauth->get('user/info');
	
	$url = "http://api.tumblr.com/v2/blog/$blog_name/post";

	$blog_post = $tum_oauth->post($url, $post_parameters);

	if (201 == $tum_oauth->http_code) {
		$msgProcessed++;
		imap_setflag_full($imap, $msgNo, "\\Seen");
		
	} else {
	  //	die('Unable to post : '. $tum_oauth->http_code);
	}
	
	//	On remet les variables � 0 pour les prochains mails
	
	$post_parameters = NULL;
	$data = NULL;
}

imap_close($imap);

die($msgProcessed." MMS proceeded !");

?>