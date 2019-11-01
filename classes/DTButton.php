<?php
	
class DTButton extends DTLayoutObject {
	
	function alternativeName() {
		
		return $this->label();
		
	}
	
	function label() {
		
		if (!is_null($label = $this->firstChild('Label/Text/StyledText'))) {
			
			return $label->text();
		
		} else if (!is_null($label = $this->firstChild('Label/Text'))) {
			
			return $label->text();
			
		} else {
			
			return '';
		}
					
	}
	
	function writeAdditionalResources() {
		
		$this->writeBinaryData('Icons/', 'IconData/');
		
	}
	
}