<?php
	
class DTHierarchical extends DTElement {
	
	public $isFolder;
	public $isEndOfFolder;
	
	public $path;
	
	function isFolder() {
		
		if (!isset($this->isFolder)) {
			
			if ($this->getAttribute('isFolder') == 'True') {
				$this->isFolder = true;	
			} else {
				$this->isFolder = false;	
			}
			
		}
		
		return $this->isFolder;
		
	}
	
	function isEndOfFolder() {
		
		if (!isset($this->isEndOfFolder)) {
			
			if ($this->getAttribute('isFolder') == 'Marker'  && $this->name() == '--') {
				$this->isEndOfFolder = true;	
			} else {
				$this->isEndOfFolder = false;	
			}
			
		}
		
		return $this->isEndOfFolder;
		
	}
					
}