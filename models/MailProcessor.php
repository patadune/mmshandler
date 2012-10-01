<?php

class MailProcessor {
	
	private $structures = array(); // Les m�tadonn�s du mail
	private $data = array(); // Les donn�es du mail
	private $post_parameters = array(); // Les donn�es transmises � l'API
	private $headers = array(); // Les en-t�tes du mail (utiles pour r�cup�rer objet, exp�diteur, etc.)
	
	private $msgNbr = 0;
	private $msgProcessed = 0;
	
	private $imapStream = null;

	public function __construct() {
		Model::load('Imap');
		$this->imapStream = Imap::open();
		$this->msgNbr = imap_num_msg($this->imapStream);
	}
	
	public function getStructures($sender = "MAILSENDER") { 
		
		for($i = 0; $i < $this->msgNbr; $i++) {
		
			$this->getHeaderInfo($i + 1, "senderaddress,Unseen");
			
			if($this->headers['senderaddress'] == $sender && $this->headers['Unseen'] == "U") {
				$this->structures[$i + 1] = imap_fetchstructure($this->imapStream, $i + 1);
			}
		}
	}
	
	public function getHeaderInfo($msgNo, $value) {
		
		$headers = imap_headerinfo($this->imapStream, $msgNo);
		$values = explode(',', $value);
		foreach($values as $v) {
			$this->headers[$v] = $headers->$v;
		}
		print_r($headers);
	}
	
	public function processMailParts() { }
	
	// @return $data[][]
	
	public function definePostType() { }
	
	
	public function MailToPost() { }
	
	// @return $post_parameters[]
	
	public function sendPost() { }
	
	public function clearVars() { }
	
	public function status() { }
}

?>