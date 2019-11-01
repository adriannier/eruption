<?php
	
class DTStyleSheet extends DTElement {
	
	public $fileExtension = '.css';
	
	function describe() {
		
		return $this->textContent;

	}
	
}