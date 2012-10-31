<?php

/**
 * CommandProcessor
 * @author re3m1s <re3m1s@gmail.com>
 *
 * Traite les commandes passées en début de message (sous la forme [t] Le message)
 *
 */

class CommandProcessor {

	private $textData;
	private $commands;

	public function __construct($textData) {
		$this->textData = $textData;
	}

	public function fetchCommands() {
		
		preg_match('/\[(.+)\]/', $this->textData, $matches);
		$this->textData = str_replace($matches[0], '', $this->textData);
		$this->commands = $matches[1];
		trim($this->textData);
		return $this->textData;
	}
}
?>