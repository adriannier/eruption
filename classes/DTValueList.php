<?php
	
class DTValueList extends DTElement {
	
	function describe() {
		
		if (!is_null($source = $this->firstChild('Source'))) {
			if ($source->attr('value') == 'Custom') {
				if (!is_null($values = $this->firstChild('CustomValues'))) {
					return trim($values->textContent);
				}			
			}
		}
		
		return $this->xml();
		
	}
	
}