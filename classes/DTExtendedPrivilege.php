<?php
	
class DTExtendedPrivilege extends DTElement {
		
	function writeAdditionalResources() {
	
		if (!is_null($list = $this->firstChild('ObjectList'))) {
			
			if (!is_null($privSetRefs = $list->childrenAtPath('PrivilegeSetReference'))) {
			
				foreach ($privSetRefs as $privSetRef) {
					
					if (!empty($privSetName = $privSetRef->name()))  {
						$directory = $this->ownerDocument->pathForResource('Security/Privilege Sets/'.filterFileName($privSetName).'/Extended Privileges/');
				
						checkDirectory($directory);
				
						$relativePath = relativePath($directory, $this->filePath());
				
						symLink($relativePath, $directory.filterFileName($this->fileName()).$this->fileExtension());
				
										
					}
			
				}
				
			}
			
				
			
		}

	}
	
}