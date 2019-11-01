<?php
	
class DTTableOccurrence extends DTElement {
	
	function directory() {
	
		if (!is_null($ref = $this->firstChild('BaseTableReference'))) {
			return $this->directory.$ref->name().DIRECTORY_SEPARATOR.'Occurrences'.DIRECTORY_SEPARATOR;
		}
		
		return $this->directory;
			
	}
		
}