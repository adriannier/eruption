<?php
	
class DTTheme extends DTElement {
	
	public $css;
	
	function describe() {
			
		$copy = $this->cloneNode();
		
		if (!is_null($meta = $this->firstChild('Metadata'))) {
			$copy->appendChild($meta->cloneNode(true));
		}
		
		$this->css = $this->firstChild('CSS', 'DTStyleSheet');
			
		return $copy->xml();

	}
	
	function fileName() {
				
		if (!empty($display = $this->attr('Display')))  {
			return $display;
		}
		
		if (!empty($name = $this->name()))  {
			return $name;
		}

		if (!empty($id = $this->id()))  {
			return $id;
		}
		
	}
		
	function writeXML($overridePath = false) {
		
		parent::write($overridePath);
		
		if (isset($this->css) && !is_null($this->css)) {

			$this->css->directory = $this->directory;
			$this->css->fileName = $this->fileName();
			$this->css->write();
			
		}
		
	}
	
}