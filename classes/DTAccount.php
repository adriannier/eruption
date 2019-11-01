<?php
	
class DTAccount extends DTElement {
	
	function name() {
		
		if (!isset($this->name)) {
			
			if (!is_null($accountName = $this->firstChild('Authentication/AccountName'))) {
			
				$text = $accountName->textContent;
	
				if (!empty($text)) {

					if ($text == "[]") {
						$this->name = 'Guest';
					} else {
						$this->name = $accountName->textContent;					
					}
	
				}
			}
				
		}
		
		return $this->name;
		
	}
	
	function writeAdditionalResources() {
	
		if (!is_null($privSetRef = $this->firstChild('PrivilegeSetReference'))) {
			
			if (!empty($privSetName = $privSetRef->name()))  {
				
				$directory = $this->ownerDocument->pathForResource('Security/Privilege Sets/'.filterFileName($privSetName).'/Accounts/');
				
				checkDirectory($directory);
				
				$relativePath = relativePath($directory, $this->filePath());
				
				symLink($relativePath, $directory.filterFileName($this->fileName()).$this->fileExtension());
				
										
			}
			
		}

	}
		
}