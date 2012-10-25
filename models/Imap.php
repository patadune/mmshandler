<?php
class Imap {
    
	private $config = array();
	
	private $fetch_options = FT_PEEK;
	private $imap_params = "/imap/ssl";
	
	public function __construct() {
	
		$this->config = Model::loadConfig('imap');
	}
	
	public function open() {
		$mailbox = '{' . $this->config['server'] . ':'.$this->config['port'].$this->imap_params.'}INBOX';
		return imap_open($mailbox, $this->config['username'], $this->config['password']);
	}
}
?>