<?php
	
class DTCustomFunction extends DTElement {
	
	public $functionName;
	public $functionPath;
	
	public $directory;
	public $fileName;
	public $fileExtension = '.txt';
	
	public $calculation;
		
	function describe() {
		
		$description = '';
		
		if (!is_null($display = $this->firstChild('Display'))) {
			$description .= '/* '.$display->text().' */'."\n\n";
		} 
						
		if (isset($this->calculation)) {
			
			$description .= $this->calculation->firstChild('Text')->textContent;			
		}
		
		return $description;

	}
					
}