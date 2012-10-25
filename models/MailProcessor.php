<?php

class MailProcessor {
	
	private $structures = array(); // Les métadonnés des mails
	
	private $msgNo = 0;
	private $data = array(); // Les données du mail
	private $post_parameters = array(); // Les données transmises à l'API
	private $headers = array(); // Les en-têtes du mail (utiles pour récupérer objet, expéditeur, etc.)
	
	private $msgNbr = 0;
	private $msgProcessed = 0;
	
	private $imap = null;
	private $imapStream = null;
	private $tumblrOAuth = null;
	
	private $debug = false;

	public function __construct() {
		Model::load('Imap');
		$this->imap = new Imap();
		$this->imapStream = $this->imap->open();
		$this->msgNbr = imap_num_msg($this->imapStream);
	}
	
	public function fetchStructures($sender = "MAILSENDER") { 
		
		for($msgNo = 1; $msgNo <= $this->msgNbr; $msgNo++) {
		
			$this->fetchHeaders($msgNo, "senderaddress,Unseen");
			
			if($this->headers['senderaddress'] == $sender && $this->headers['Unseen'] == "U") {
				$this->structures[$msgNo] = imap_fetchstructure($this->imapStream, $msgNo);
			}
		}
		return 0;
	}
	
	public function fetchHeaders($msgNo, $value) {
		
		$headers = imap_headerinfo($this->imapStream, $msgNo);
		$values = explode(',', $value);
		foreach($values as $v) {
			if(isset($headers->$v)) {$this->headers[$v] = $headers->$v;}
		}
		//	if($this->debug){print_r($headers);}
		return 0;
	}
	
	public function processMailParts($msgNo, $structure) { 
		
		$this->msgNo = $msgNo;
		$this->fetchHeaders($msgNo, "subject");
		
		foreach($structure->parts as $partNo => $part) {
		
		/**
		 *	Types des parties de mails
		 *	==========================
		 *
		 *	*Type 0 : text
		 *	Type 1 : multipart
		 *	Type 2 : message
		 *	Type 3 : application
		 *	*Type 4 : audio
		 *	*Type 5 : image
		 *	*Type 6 : video
		 *	Type 7 : other
		 *
		 **/
		
			switch($part->type) {
			
				case 0:	
					if($part->parameters[1]->value != "banniere.txt") { //	Supprime l'entête d'Orange
						$this->data[$partNo]['id'] = $partNo;
						$this->data[$partNo]['type'] = 'text';
						$this->data[$partNo]['data'] = base64_decode(imap_fetchbody($this->imapStream, $msgNo, $partNo + 1, Imap::fetch_options));
					}
					break;				
				
				case 4:
					$this->data[$partNo]['id'] = $partNo;
					$this->data[$partNo]['type'] = 'audio';
					$this->data[$partNo]['data'] = base64_decode(imap_fetchbody($this->imapStream, $msgNo, $partNo + 1, Imap::fetch_options));
					break;
				
				case 5:
					if($part->parameters[0]->value != "logo.gif") {	//	Enlève le logo d'Orange
						$this->data[$partNo]['id'] = $partNo;
						$this->data[$partNo]['type'] = 'photo';
						$this->data[$partNo]['data'] = base64_decode(imap_fetchbody($this->imapStream, $msgNo, $partNo + 1, Imap::fetch_options));
					}
					break;
				
				case 6:
					$this->data[$partNo]['id'] = $partNo;
					$this->data[$partNo]['type'] = 'video';
					$this->data[$partNo]['data'] = base64_decode(imap_fetchbody($this->imapStream, $msgNo, $partNo + 1, Imap::fetch_options));
					break;
			}
		}
		if($this->debug){print_r($this->data);}
	}
	
	// @return $data[][]
	
	public function definePostType() {
		
		$this->post_parameters['type'] = "text";
		foreach($this->data as $d) {
			if($d['type'] != "text") {
				$this->post_parameters['type'] = $d['type'];
			}
		}
		//	if($this->debug){print_r($this->post_parameters);}
	}
	
	
	public function MailToPost() { 
	
		if((isset($this->headers['subject'])) && $this->post_parameters['type'] == "text") {
			$this->post_parameters['title'] = iconv_mime_decode($this->headers['subject']);
		}
		
		foreach($this->data as $d) {
			switch($d['type']) {
				
				case "text":
					if($this->post_parameters['type'] == "text") {$textField = "body";}
					else {$textField = "caption";}
						
						if(!empty($this->post_parameters[$textField])) 
							{$this->post_parameters[$textField] .= "<br />" . $d['data'];}	// On rajoute à la suite
						else {$this->post_parameters[$textField] = $d['data'];}
					break;
				
				case "photo":
					$this->post_parameters['data'] = $d['data'];
					break;
					
				case "audio":
					$this->post_parameters['data'] = $d['data'];
					break;
					
				case "video":
					$this->post_parameters['data'] = $d['data'];
					break;
			}
		}
		if($this->debug){print_r($this->post_parameters);}
	}
	
	// @return $post_parameters[]
	
	public function sendPost() {
		
		$tumblrConfig = Model::loadConfig('oauth');
		
		if(empty($this->tumblrOAuth)) {
			Model::load('TumblrOAuth');
			$this->tumblrOAuth = new TumblrOAuth($tumblrConfig);
		}
		
		$url = "http://api.tumblr.com/v2/blog/".$tumblrConfig['blog_name']."/post";
		$blog_post = $this->tumblrOAuth->post($url, $this->post_parameters);
		
		if (201 == $this->tumblrOAuth->http_code) {
			$this->msgProcessed++;
			imap_setflag_full($this->imapStream, $this->msgNo, "\\Seen");
		}
	}
	
	public function clearVars() {
		$this->structures = $this->data = $this->post_parameters = $this->headers = array();
		$this->msgNo = 0;
	}
	
	public function debugMode($v) {if(is_bool($v)){$this->debug = $v;}}
	
	public function listChosenMails() {
		
		foreach($this->structures as $msgNo => $structure) {
			echo "<pre>";
			echo "<h3>Mail n°" . $msgNo . " !</h3>";
		}
	}
	
	public function getStructures() {return $this->structures;}
	
	public function setTweet($value) {$this->post_parameters['tweet'] = $value;}
	
	public function __destruct() {
		imap_close($this->imapStream);
		if($this->debug) {$this->listChosenMails();}
		else {echo $this->msgProcessed ." MMS Proceeded !";}
	}
}

?>