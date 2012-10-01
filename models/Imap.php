<?php
class Imap {
    
	const server = "imap.gmail.com";
	const port = "993";
	const imap_params = "/imap/ssl";
	
	const username = "USERNAME";
	const password = "PASSWORD";
	
	const fetch_options = FT_PEEK;
	
	static function open() {
		$mailbox = '{' . Imap::server . ':'.Imap::port.Imap::imap_params.'}INBOX';
		return imap_open($mailbox, Imap::username, Imap::password);
	}
}
?>