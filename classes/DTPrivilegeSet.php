<?php
	
class DTPrivilegeSet extends DTElement {
	
	function directory() {
		
		return parent::directory().filterFileName($this->name()).DIRECTORY_SEPARATOR;
		
	}
	
}