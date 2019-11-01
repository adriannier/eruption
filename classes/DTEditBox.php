<?php
	
class DTEditBox extends DTLayoutObject {
	
	function alternativeName() {
		
		return $this->fieldName();
		
	}
	
	function fieldName() {
		
		if (!is_null($fieldRef = $this->firstChild('FieldReference'))) {
					
			return $fieldRef->attr('tableOccurrence').'::'.$fieldRef->name();
									
		} else {
			
			return '';
			
		}	
				
	}
	
}