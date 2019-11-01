<?php
	
class DTLayout extends DTHierarchical {
	
	public $parts;
	
	function preProcess() {
		
		if (!is_null($partList = $this->firstChild('PartsList'))) {
			
			$this->parts = $partList->childrenAtPath('Part', 'DTLayoutPart');
			
			$partIndex = 0;
			$partCount = count($this->parts);
			
			foreach ($this->parts as $part) {
				
				$part->index = $partIndex;
				$part->siblingCount = $partCount;
				$part->parentElement = $this;
				$partIndex++;
				
			}
			
		}
		
	}
	
	function process() {

		$copy = $this->cloneNode();
		
		if (!is_null($tableRef = $this->firstChild('TableOccurrenceReference'))) {
			$copy->appendChild($tableRef->cloneNode(true));
		}
		
		if (!is_null($themeRef = $this->firstChild('LayoutThemeReference'))) {
			$copy->appendChild($themeRef->cloneNode(true));
		}
				
		return $copy->xml();

	}
	
	function write($overridePath = false) {
		
		$this->fileName = str_pad((string) 0, strlen((string) count($this->parts)), '0', STR_PAD_LEFT).' Layout';
		
		parent::write($overridePath);
		
		foreach ($this->parts as $part) {
			
			$part->directory = $this->directory;
			
			$part->write();
				
		}
		
	}
	
}