<?php
	
class DTLayoutPart extends DTElement {
	
	public $objects;
	
	function preProcess() {
		
		if (!is_null($objectList = $this->firstChild('ObjectList'))) {
			
			$this->objects = $objectList->childrenAtPath('LayoutObject', 'DTLayoutObject');
			
			$objIndex = 0;
			$objCount = count($this->objects);
			
			foreach ($this->objects as $obj) {
				
				$obj->index = $objIndex;
				$obj->siblingCount = $objCount;
				$obj->parentElement = $this;
				$objIndex++;
				
			}
			
		}
		
	}
	
	function process() {
			
		$copy = $this->cloneNode();
		
		if (!is_null($def = $this->firstChild('Definition'))) {
			$copy->appendChild($def->cloneNode(true));
		}
			
		return $copy->xml();

	}
	
	function hasObjects() {
		
		if (isset($this->objects) && count($this->objects) > 0) {
			return true;	
		} else {
			return false;
		}
	}
	
	function fileName() {
	
		if ($this->hasObjects()) {
			
			return $this->objects[0]->paddedSiblingNumber(0).' '.$this->type();
		
		} else {
			
			return $this->paddedSiblingNumber().' '.$this->type();
			
		}
	}
	
	function directory() {
		
		if ($this->hasObjects()) {
			return $this->directory.$this->folderNameForObjects().DIRECTORY_SEPARATOR;
		} else {
			return $this->directory;
		}
		
	}
	
	function folderNameForObjects() {
		
		return filterFileName($this->paddedSiblingNumber().' '.$this->type());
		
	}
	
	function write($overridePath = false) {

		parent::write($overridePath);
		
		if ($this->hasObjects()) {
		
			foreach ($this->objects as $obj) {
			
				$obj->directory = $this->directory();
				$obj->write();
					
			}
			
		}
		
		
	}
	
}