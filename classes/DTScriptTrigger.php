<?php
	
class DTScriptTrigger extends DTElement {
	
	function name() {
		
		if (!isset($this->name)) {
			$this->name = $this->attr('action');
		}
		
		return $this->name;
		
	}
	
	function directory() {
		
		return parent::directory().filterFileName($this->name()).DIRECTORY_SEPARATOR;
		
	}
	
	function writeAdditionalResources() {
		
		if (!is_null($scriptRef = $this->firstChild('ScriptReference'))) {
		
			if (!empty($scriptId = $scriptRef->id())) {

				if (!is_null($script = $this->ownerDocument->scriptObjects[$scriptId])) {
				
					$relativePath = relativePath($this->directory(), $script->filePath());
					
					symLink($relativePath, $this->directory().'Script.txt');
									
				}
				
			}
			
		}
		
	}
}