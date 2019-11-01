<?php
	
class DTLayoutGroup extends DTLayoutObject {
	
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
	
	function hasObjects() {
		
		if (isset($this->objects) && count($this->objects) > 0) {
			return true;	
		} else {
			return false;
		}
	}
	
	function fileName() {
	
		if ($this->hasObjects()) {
			
			return $this->objects[0]->paddedSiblingNumber(0).' '.$this->container->type();
		
		} else {
			
			return $this->paddedSiblingNumber().' '.$this->container->type();
			
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
		
		return filterFileName($this->paddedSiblingNumber().' '.$this->container->type());
		
	}
	
	function writeWithContainer() {
		
		$this->preProcess();
		
		$copy = $this->container->cloneNode(true);
		
		// Remove objects in ObjectList
		if (!is_null($objectList = $copy->firstChild('GroupedButton/ObjectList'))) {
			if (!is_null($objects = $objectList->childrenAtPath('LayoutObject'))) {
				$objectsReversed = array_reverse($objects);
				foreach ($objectsReversed as $obj) {
					$objectList->removeChild($obj);
				}
			}
			$objectList->nodeValue = '';
		}
		
		$this->writeData($copy->xml());
		
		if ($this->hasObjects()) {
		
			foreach ($this->objects as $obj) {
			
				$obj->directory = $this->directory();
				$obj->write();
					
			}
			
		}
		
	}
	
}