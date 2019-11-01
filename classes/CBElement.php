<?php
	
class CBElement {
	
	public $className;
	
	public $processor;
	public $scriptFormat = 'txt';
	public $customFunctionFormat = 'txt';
	
	public $index;
	public $id;
	public $type;
	public $tagName;
	public $name;
	public $parentElement = false;
	public $siblingNumber;
	
	
	public $addSuffixToFileName = true;
	public $cachedFolderPath = false;
	public $cachedFileSystemName = false;
	
	public $pathComponents;
	public $path;
	public $childCount = [];
	
	public $cachedChildren = false;
	public $hasProcessableChildren;
	
	public $indentation = 0;
	
	private $simpleXML;
	
	public $elementTypesWithProcessableChildren = [
		'BaseTable',
		'FMObjectList',
		'Group', 
		'GroupButton', 
		'GroupButtonObj', 
		'Layout', 
		'LayoutObjectList',
		'Portal', 
		'PortalObj',
		'ScriptCatalog',
		'TabControl', 
		'TabControlObj', 
		'TabPanel', 
		'TabPanelObj'
	];
	
	public $ignoredByPath = false;
	public $ignoredPaths = [];
	
	public $doNotWriteAttributes = [
		'BaseTable',
		'ScriptCatalog',
		'Group'
	];
	
	public $stepsIncreasingIndentation = [
		'If',
		'Loop',
		'Else If',
		'Else'
	];
	
	public $stepsDecreasingIndentation = [
		'End If',
		'End Loop',
		'Else If',
		'Else'
	];
	
	function __construct($processor, $obj, $objIndex, $parentObj = false) {
		
		$this->className = get_class($this);

		$this->processor = $processor;
		
		$this->simpleXML = $obj;
		$this->simpleXML->addAttribute('index', $objIndex);
		$this->id = (string) $this->simpleXML['id'];
		$this->tagName = $this->simpleXML->getName();		
		$this->parentElement = $parentObj;
		$this->index = $objIndex;
		
		$this->initPath();
		
		if (!$this->ignoredByPath) {
			
			// doLog($this->path);
						
			// Set type
			if (isset($this->simpleXML['type']) && (string) $this->simpleXML['type'] != '') {
				$this->type = (string) $this->simpleXML['type'];
			} else {
				$this->type = $this->tagName;
			}
			
			// Set name
			if (isset($this->simpleXML['name'])) {
				$this->name = (string) $this->simpleXML['name'];
			} else {
				$this->name = '';
			}
			
			// Set processable children
			$this->hasProcessableChildren = false;
			if (in_array($this->type, $this->elementTypesWithProcessableChildren)) {
				if (count($this->children()) > 0) {
					$this->hasProcessableChildren = true;	
				}
			}
			
			if ($this->type == 'Script') {
				$n = 1;
				foreach($this->simpleXML->children() as $child) { 
					$child->addAttribute('step_number', $n);
					$n++;
						 
				 }
			}
			
			if ($this->type == 'Step') {
				if (in_array($this->name, $this->stepsDecreasingIndentation)) {
					$this->parentElement->indentation--;
				}
			}
			
			if ($this->parentElement !== false) {
				$this->indentation = $this->parentElement->indentation;
			}
			
			if ($this->type == 'Step') {
				if (in_array($this->name, $this->stepsIncreasingIndentation)) {
					$this->parentElement->indentation++;
				}
			}
			
		}
		
	}
	
	function initPath() {
		
		if ($this->parentElement === false) {
			
			$this->pathComponents = array();
			$this->siblingNumber = 1;
			
		} else {
			$this->pathComponents = $this->parentElement->pathComponents;
			if (!isset($this->parentElement->childCount[$this->tagName])) {
				$this->parentElement->childCount[$this->tagName] = 1;
			} else {
				$this->parentElement->childCount[$this->tagName]++;
			}
			$this->siblingNumber = $this->parentElement->childCount[$this->tagName];
		}
		
		array_push($this->pathComponents, $this->tagName.'['.$this->siblingNumber.']');
		$this->path = '/'.implode('/', $this->pathComponents);
		
		if (in_array($this->path, $this->ignoredPaths)) {
			$this->ignoredByPath = true;	
		}

	}
	
	function hasParent() {
		
		if ($this->parentElement === false) {
			return false;
		} else {
			return true;
		}
		
	}
	
	function children() {
			
		if ($this->cachedChildren === false) {
			
			$children = array();
			$index = 0;
			
			foreach($this->simpleXML->children() as $child) { 
				
				array_push($children, new $this->className($this->processor, $child, $index, $this));
				$index++;
					 
			}
			 
			 $this->cachedChildren = $children;

		}
		
		return $this->cachedChildren;
			
	}
   
	function folderPath() {
	
		if (!$this->hasParent()) {
			return '';	  
		}
		
		if ($this->cachedFolderPath === false) {
		   
			if ($this->parentElement !== false) {
				$this->cachedFolderPath = $this->parentElement->folderPath().$this->parentElement->fileSystemName().'/';	
			} else {
				$this->cachedFolderPath = '/'; 
			}	
		}
	
		return $this->cachedFolderPath;
	  
	}

	function description() {
		
		if ( $this->type == 'Step' ) {
			return $this->stepDescription();
		} else {
			return $this->name;
		}
				
	}
	
	function fileSystemName($n = false) {
	   
		if (!$this->hasParent()) {
			return '';	  
		}
		
		if ( $this->cachedFileSystemName === false ) {
		
			$name = $this->name;
						
			$doNotPrefixType = ['Script', 'Group', 'Step', 'CustomFunction', 'BaseTable', 'Field'];
			$doNotPrefixIndex = ['Script', 'Group', 'BaseTable', 'CustomFunction', 'Layout'];
			$doNotSuffixId = ['CustomFunction', 'Step', 'Group', 'Script', 'Layout'];
			
			$prefixType = true;
			$prefixIndex = true;
			$suffixId = true;
			
			if (in_array($this->type, $doNotPrefixIndex)) {
				$prefixIndex = false;	 
			}
			
			if (in_array($this->type, $doNotSuffixId)) {
				$suffixId = false;	 
			}
			
			if (in_array($this->type, $doNotPrefixType)) {
				$prefixType = false;
			}
			
			if ( $this->type == 'Script' ) {
	
				if ( $this->name == '-' ) {
					$name = '----------------------------';				
				} else {
					$name = $this->name;
				}
				
			} else if ( $this->type == 'Step' ) {
	
				$name = $this->stepDescription();
	
			} else if ( $this->type == 'Field' && isset($this->simpleXML->FieldObj->Name)) {

				// Field as part of layout
				$name = (string) $this->simpleXML->FieldObj->Name;	  
			   
			} else if ( $this->type == 'Field' && isset($this->id) && $this->id != '') {

				// Field as part of table
				$prefixIndex = false;
				$suffixId = false;
				
			} else if ( $this->type == 'Button' ) {
								   
				if ($name == '' && isset($this->simpleXML->ButtonObj->Step->Script['name'])) {
					$name = (string) $this->simpleXML->ButtonObj->Step->Script['name'];				 
				}
									  
			}
			
			if ($prefixType) {
				
				if ($name == '') {
					$name = $this->type;
				} else {
					$name = $this->type.' '.$name;
				}
				
			}
			
			if ($prefixIndex) {
				$index = str_pad($this->index + 1, 3, '0', STR_PAD_LEFT);
				if ($name == '') {
					$name = $index;
				} else {
					$name = $index.' '.$name;
				}
			}
			
			if ($suffixId && $this->id != '') {
				$name .= ' ('.$this->id.')';
			}
			
			$this->cachedFileSystemName = $name;
	   }
	   
		if ($n !== false) {
			return filterFileName($this->cachedFileSystemName.' '.$n);
		} else {
			return filterFileName($this->cachedFileSystemName);
		}

	   
	}
	  
	function fileName($n = false) {
	   
		if ($this->type == 'Script' && $this->name == '-') {
			return $this->fileSystemName();
			
		} else if ($this->type == 'Script' && $this->scriptFormat == 'txt') {
			return $this->fileSystemName($n).'.txt';
	
		} else if ($this->type == 'CustomFunction' && $this->customFunctionFormat == 'txt') {
			return $this->fileSystemName($n).'.txt';
					
		} else if ($this->type == 'Step' && $this->name == '# (comment') {
			return $this->fileSystemName();
						
		} else if ($this->addSuffixToFileName) {
			return $this->fileSystemName($n).'.xml';
			
		} else {
			return $this->fileSystemName($n);
		}
			  
	}
	
	function filePath($n = false) {
	
		return $this->folderPath().$this->fileName($n);		
	  
	}
	
	function writeWithBaseDirectory($baseDirectory) {
		
		if (!$this->ignoredByPath) {
			
			if (!substr($baseDirectory, -1) == '/') {
				$baseDirectory .= '/';
			}
		   
			if ($this->hasProcessableChildren) {
				
				checkDirectory($baseDirectory.$this->folderPath().$this->fileSystemName().'/');
				
				if ($this->hasParent() && !in_array($this->type, $this->doNotWriteAttributes)) {
						
					$attributes = array();
					
					foreach($this->simpleXML->attributes() as $a => $b) {
						array_push($attributes, $a . '="'. $b .'"');
					}
					
					$str = '<'.$this->type.' '.implode(' ', $attributes).'/>';
		
					file_put_contents($baseDirectory.$this->folderPath().$this->fileSystemName().'/_Attributes.xml', $str);
					
				}
				
			} else {
				
				checkDirectory($baseDirectory.$this->folderPath());
				
				if (file_exists($baseDirectory.$this->filePath())) {
				   // doLog('File exists! '. $baseDirectory.$this->filePath()."\n"); 
				}
				
				if ($this->type == 'Script' && $this->scriptFormat == 'txt') {
					
					file_put_contents($baseDirectory.$this->filePath(), $this->scriptAsText());
	
				} else if ($this->type == 'CustomFunction' && $this->customFunctionFormat == 'txt') {
					
					file_put_contents($baseDirectory.$this->filePath(), $this->customFunctionAsText());
						
				} else {
					
					$formattedXML = formatXML($this->simpleXML->asXML());
					file_put_contents($baseDirectory.$this->filePath(), $formattedXML);
					
				}
				
			}
		
		}

	}
	
	// !Script
	
	function scriptAsText() {
		
		$stepDescriptions = array();
		$stepNumber = 1;
		foreach($this->children() as $child) { 
					
			array_push($stepDescriptions, $child->stepDescription());
			$stepNumber++;	 
		}
		
		return implode("\n", $stepDescriptions);
	}
	
	function stepDescriber() {
	
		return $this->processor->stepDescriber;
		
	}
	
	function stepDescription() {
		
		$stepName = $this->name;
		
		// debugLog('Getting step description for: '.$stepName);
		
		$methodName = methodNameForScriptStepName($stepName);
		
		if ($methodName == '_') {
		
			$str = '';
			
		} else {
			
			$this->stepDescriber()->element = $this;
			$this->stepDescriber()->simpleXML = $this->simpleXML;
			
			if (method_exists($this->stepDescriber(), $methodName)) {
				
				$str = $this->stepDescriber()->{$methodName}();
				
			} else {
				
				// Method not defined in CBScriptStepDescriber
				
				if (!in_array($stepName, $this->stepDescriber()->missingScriptStepsDescriptions)) {
					
					doLog("Using default description for script step *".$stepName."*. Add method ".$methodName."() to class ".get_class($this->stepDescriber())." to avoid this warning.");
					array_push($this->stepDescriber()->missingScriptStepsDescriptions, $stepName);
									
				
				}
				
				$str = $this->stepDescriber()->defaultDescription();

			}

			
			for ( $i = 0 ; $i < $this->indentation ; $i ++ ) {
				if ($this->scriptFormat = 'txt') {
					$str = "\t".$str;
				} else {
					$str = '    '.$str;
				}
	
			}
		
		}		
		
		return $str;
					
	}
	
	// !Custom Function
		
	function customFunctionAsText() {

		$name = $this->simpleXML['name'];
		$id = $this->simpleXML['id'];
		$parameters = $this->simpleXML['parameters'];
		
		$description = '/*'.PHP_EOL;
		$description .= "\tCustom Function #".$id.PHP_EOL;
		$description .= "\t".$name.' ('.str_replace(';', ', ', $this->simpleXML['parameters']).')'.PHP_EOL;
		$description .= '*/';

		$text = (string) $this->simpleXML->Calculation;
		
		return $description."\n\n".$text;
		
	}
	
}