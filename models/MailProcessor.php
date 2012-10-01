<?php

class MailProcessor {
	
	private $msgNbr = 0;
	private $msgProcessed = 0;
	
	private $imapStream = null;

	public function __construct() {
		Model::load('Imap');
		$this->imapStream = Imap::open();
	}
	
	public function getStructures($sender = "MAILSENDER") { }
	
	// @return $structures[]
	
	public function getHeaderInfo($msgNo) { }
	
	public function processMailParts() { }
	
	// @return $data[][]
	
	public function definePostType() { }
	
	
	public function MailToPost() { }
	
	// @return $post_parameters[]
	
	public function sendPost() { }
	
	public function clearVars() { }
	
	public function status() { }
}