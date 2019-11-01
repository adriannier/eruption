<?php
	
class DTTableRelationship extends DTElement {
	
	function defaultDirectory() {
		
		return $this->ownerDocument->pathForResource('Relationships/');	
		
	}
	
	function fileNameForSide($side) {
		
		if (!is_null($left = $this->firstChild('LeftTable'))) {
			
			if (!is_null($right = $this->firstChild('RightTable'))) {
	
				if ($side == 'Right') {
					return filterFileName($right->name().' < '.$left->name());
				} else {
					return filterFileName($left->name().' > '.$right->name());
				}

							
			}	
					
		}
		
	}
	
	function writeXML($overridePath = false) {
		
		$baseDirectory = $this->directory;
		
		$this->writeXMLForTable('Default');
		$this->writeXMLForTable('Left', $baseDirectory);
		$this->writeXMLForTable('Right', $baseDirectory);
		
		$this->directory = $baseDirectory;
		
		
	}
	
	function writeXMLForTable($side, $baseDirectory = false) {
		
		if ($baseDirectory === false) {
			$baseDirectory = $this->defaultDirectory();	
		}
		
		$fileName = $this->fileNameForSide($side);
		
		if ($side == 'Default') {

			if (!is_null($ref = $this->firstChild('JoinPredicateList/JoinPredicate/LeftField/FieldReference'))) {
				
				$table = $ref->attr('baseTable');
				
				if (!empty($table)) {
					
					$this->directory = $baseDirectory;
					$this->fileName = $fileName;
					parent::writeXML();	
						
				}
				
			}
			
		} else {
		
			if (!is_null($ref = $this->firstChild('JoinPredicateList/JoinPredicate/'.$side.'Field/FieldReference'))) {
			
				$table = $ref->attr('baseTable');
				
				if (!empty($table)) {
					
					$directory = $baseDirectory.$table.DIRECTORY_SEPARATOR.'Relationships'.DIRECTORY_SEPARATOR;
					$fileName = $this->fileNameForSide($side);
					
					$targetFilePath = $this->defaultDirectory().$this->fileNameForSide('Default').$this->fileExtension();
					$linkFilePath = $directory.filterFileName($fileName).$this->fileExtension();
					
					checkDirectory($directory);
					symlink($targetFilePath, $linkFilePath);
					
				}
				
			}
	
		}
				
	}
	
}