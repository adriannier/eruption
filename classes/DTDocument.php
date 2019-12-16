<?php
	
class DTDocument extends DOMDocument {
	
	public $processor;
	
	public $xPath;
	
	public $queryCount = 0;
	
	public $functionObjects;
	public $scriptObjects;
	public $layoutObjects;
	public $valueListObjects;
	public $namedObjects;
	
	public $missingDescriptionMethods = [];
	
	public $debugScriptName;
	public $debugScriptId;
	
	public $debugLayoutName;
	public $debugLayoutId;
	public $fmFileName;
	
	public $rootTagName;
	
	function fmFileName() {
		
		if (!isset($this->fmFileName)) {
			
			if (!is_null($firstChild = $this->childNodes[0])) {
				if (!empty($fileName = $firstChild->getAttribute('File'))) {
					$this->fmFileName = pathinfo($fileName, PATHINFO_FILENAME);
				}
			}
			
		}

		return $this->fmFileName;
	}
	
	function destinationDirectory() {
		
		if (!empty($fileName = $this->fmFileName())) {
			$subdirectory = $fileName.DIRECTORY_SEPARATOR;
		} else {
			$subdirectory = '';
		}
		
		return $this->processor->destinationDirectory.$subdirectory;
		
	}
	
	function pathForResource($resourceName) {
		
		$resourceName = str_replace('/', DIRECTORY_SEPARATOR, $resourceName);
		return $this->destinationDirectory().$resourceName;
		
	}
	
	function initializeDestinationDirectory() {
	
		if (is_dir($this->destinationDirectory())) {
			rmdirRecursive($this->destinationDirectory());
		}
		
		mkdir($this->destinationDirectory(), 0777, true);
		
		if (!is_dir($this->destinationDirectory())) {
			
			doLog('Could not create destination directory at: '.$this->destinationDirectory());
			exit(1);
		}
		
		debugLog('Initialized destination directory at '.$this->destinationDirectory());
	
	}		
		
	function process() {
		
		debugLog('Processing template');
		
		$this->rootTagName = $this->documentElement->tagName;
		$this->xPath = new DOMXPath($this);
		
		$this->initializeDestinationDirectory();
		
		$this->processFoundNodes(
			'accounts',
			'Security/Accounts',
			'/'.$this->rootTagName.'[1]/Structure[1]/AddAction[1]/AccountsCatalog[1]/ObjectList[1]/Account',
			'DTAccount'
		);

		$this->processFoundNodes(
			'privilege sets',
			'Security/Privilege Sets',
			'/'.$this->rootTagName.'[1]/Structure[1]/AddAction[1]/PrivilegeSetsCatalog[1]/ObjectList[1]/PrivilegeSet',
			'DTPrivilegeSet'
		);

		$this->processFoundNodes(
			'extended privileges',
			'Security/Extended Privileges',
			'/'.$this->rootTagName.'[1]/Structure[1]/AddAction[1]/ExtendedPrivilegesCatalog[1]/ObjectList[1]/ExtendedPrivilege',
			'DTExtendedPrivilege'
		);
		
		$this->processFoundNodes(
			'table occurrences',
			'Tables',
			'/'.$this->rootTagName.'[1]/Structure[1]/AddAction[1]/TableOccurrenceCatalog[1]/TableOccurrence',
			'DTTableOccurrence'
		);
		
		$this->processTables();
		
		$this->processFunctions();
		
		$this->processScripts();
		
		$this->processLayouts();
		
		$this->processValueLists();
		
		$this->processFoundNodes(
			'script triggers',
			'Script Triggers',
			'/'.$this->rootTagName.'[1]/Metadata[1]/AddAction[1]/ScriptTriggers[1]/ScriptTrigger',
			'DTScriptTrigger'
		);
		
		$this->processFoundNodes(
			'relationships',
			'Tables',
			'/'.$this->rootTagName.'[1]/Structure[1]/AddAction[1]/RelationshipCatalog[1]/Relationship',
			'DTTableRelationship'
		);
		
		$this->processFoundNodes(
			'themes',
			'Themes',
			'/'.$this->rootTagName.'[1]/Structure[1]/AddAction[1]/ThemeCatalog[1]/Theme',
			'DTTheme'	
		);
		
		$this->processFoundNodes(
			'base directories',
			'Base Directories',
			'/'.$this->rootTagName.'[1]/Structure[1]/AddAction[1]/BaseDirectoryCatalog[1]/BaseDirectory'
		);

		if (defined('DEBUG') && DEBUG === true) {
						
			debugLog('Query count: '.$this->queryCount);
	
			$this->logMissingDescriptionMethods();
		
		}
			
	}
	
	function processFoundNodes($nodeDescription, $directoryName, $xPath, $customClass = 'DTElement') {
		
		debugLog('Processing '.$nodeDescription);
		
		// Find nodes
		$nodes = $this->q($xPath, $customClass);
		
		if ($customClass != 'DTElement') {
			$this->namedObjects[$customClass] = [];
		}
		
		$directory = $this->pathForResource($directoryName.'/');
		$count = 0;
		
		foreach ($nodes as $node) {
			
			$count++;
			
			if ($customClass != 'DTElement' && !empty($node->id())) {
				$this->namedObjects[$customClass][$node->id()] = $node;
			}
		
			$node->directory = $directory;
			
			$node->writeXML();
		
		}
		
		doLog(ucfirst($nodeDescription).': '.$count);
		
	}
	
	function processTables() {

		debugLog('Processing tables');

		// Find field catalogs
		$fieldCatalogs = $this->q('/'.$this->rootTagName.'[1]/Structure[1]/AddAction[1]/FieldsForTables[1]/FieldCatalog');

		$tableCount = 0;
		$fieldCount = 0;
		
		foreach ($fieldCatalogs as $fieldCatalog) {
			
			if (!is_null($tableOccurrenceRef = $fieldCatalog->firstChild('TableOccurrenceReference'))) {
				
				if (!is_null($fieldList = $fieldCatalog->firstChild('ObjectList'))) {
					
					$tableOccurrenceId = $tableOccurrenceRef->id();
					
					if (!is_null($tableOccurrence = $this->namedObjects['DTTableOccurrence'][$tableOccurrenceId])) {
						
						if (!is_null($tableRef = $tableOccurrence->firstChild('BaseTableReference'))) {
							
							$tableName = $tableRef->name();
							$tableId = $tableRef->id();
							$tableCount++;
									
							// Generate directory path for this table
							$tableDirectory = $this->pathForResource('Tables/'.filterFileName($tableName).'/Fields/');
							
							checkDirectory($tableDirectory);
							
							foreach ($fieldList->childNodes as $field) {
								
								if ($field->nodeName == 'Field') {
									
									$fieldCount++;
									
									$fieldFilePath = $tableDirectory.filterFileName($field->name()).'.txt';
									file_put_contents($fieldFilePath, $field->xml());
									
								}
							}			

						}
												
					}
					
				}
				
			} 
			
		}
	
		doLog('Tables: '.$tableCount);
		doLog('Fields: '.$fieldCount);
	
	}
		
	function processFunctions() {
	
		debugLog('Processing custom functions');
		
		// Find custom function nodes
		$functions = $this->q('/'.$this->rootTagName.'[1]/Structure[1]/AddAction[1]/CustomFunctionsCatalog[1]/ObjectList[1]/CustomFunction', 'DTCustomFunction');
		
		$this->functionObjects = [];

		$functionsDirectory = $this->pathForResource('Custom Functions/');
		$functionCount = 0;
		
		foreach ($functions as $function) {
			
			$functionCount++;
			
			$function->directory = $functionsDirectory;
			$function->fileName = filterFileName($function->name());
			
			$this->functionObjects[$function->id()] = $function;
		
		}
		
		// Find calculations for custom functions
		$functions = $this->q('/'.$this->rootTagName.'[1]/Structure[1]/AddAction[1]/CalcsForCustomFunctions[1]/ObjectList[1]/CustomFunctionCalc');
		
		foreach ($functions as $function) {
			
			$functionId = $function->firstChild('CustomFunctionReference')->id();
						
			if (isset($this->functionObjects[$functionId])) {
				
				$this->functionObjects[$functionId]->calculation = $function->firstChild('Calculation');
					
				$this->functionObjects[$functionId]->write();
				
			}
							
		}
		
		doLog('Custom Functions: '.$functionCount);
			
	}
	
	function processScripts() {
	
		debugLog('Processing scripts');
		
		// Find script nodes
		$scripts = $this->q('/'.$this->rootTagName.'[1]/Structure[1]/AddAction[1]/ScriptCatalog[1]/Script', 'DTScript');

		$this->scriptObjects = [];
		$pathComponents = [];
		
		if (!defined('DEBUG') || DEBUG !== true) {
			unset($this->debugScriptName);
		}
		
		foreach ($scripts as $script) {
			
			// debugLog($scriptId.' '.$scriptName.' '.$script->getNodePath());
			
			if ($script->isFolder()) {
				
				// Folder
				array_push($pathComponents, filterFileName($script->name()));
				
			} else if ($script->isEndOfFolder()) {
				
				// End of folder
				array_pop($pathComponents);
				
			} else {
				
				// Script
				
				// Watch out! For performance reasons the debugScriptId property is set in the following condition
				if (!isset($this->debugScriptName) || ($this->debugScriptName == $script->name() && $this->debugScriptId = $script->id())) {
					
					$script->path = implode('/', $pathComponents);
					
					// Generate path for this script
					$scriptDirectory = $this->pathForResource('Scripts/'.$script->path.'/');
					
					$script->directory = $scriptDirectory;
					$script->fileName = filterFileName($script->name());
					
					$this->scriptObjects[$script->id()] = $script;
				
				}
				
			}

		}
		
		$scriptCount = count($this->scriptObjects);
		
		// Find script step nodes
		$scripts = $this->q('/'.$this->rootTagName.'[1]/Structure[1]/AddAction[1]/StepsForScripts[1]/Script');
		$scriptIndex = 0;
		
		foreach ($scripts as $script) {		
			
			$scriptId =	$script->firstChild('ScriptReference')->id();
			
			if (!isset($this->debugScriptId) || $scriptId == $this->debugScriptId) {

				$scriptStepList = $script->firstChild('ObjectList');
				
				$s = $this->scriptObjects[$scriptId];
				
				if (isset($s)) {
					
					$s->addSteps($scriptStepList->childNodes);
				
					debugLog('### Script '.($scriptIndex + 1).'/'.$scriptCount.' (name: '.$s->name().', id: '.$scriptId.')');
					
					$s->write();
					
				}
						
				$scriptIndex++;
			
			}
			
		}
		
		if (isset($this->debugScriptId)) {
			d($s->describe());
		}
		
		doLog('Scripts: '.$scriptCount);
	
	}
	
	function processLayouts() {
	
		debugLog('Processing layouts');
		
		// Find layout nodes
		$layouts = $this->q('/'.$this->rootTagName.'[1]/Structure[1]/AddAction[1]/LayoutCatalog[1]/Layout', 'DTLayout');

		$this->layoutObjects = [];
		$pathComponents = [];
		
		if (!defined('DEBUG') || DEBUG !== true) {
			unset($this->debugLayoutName);
		}
		
		foreach ($layouts as $layout) {
						
			if ($layout->isFolder() == 'True') {
				
				// Folder
				array_push($pathComponents, filterFileName($layout->name()));
				
			} else if ($layout->isEndOfFolder()) {
				
				// End of folder
				array_pop($pathComponents);
				
			} else {
				
				
				// Watch out! For performance reasons the debugLayoutId property is set in the following condition
				if (!isset($this->debugLayoutName) || ($this->debugLayoutName == $layout->name() && $this->debugLayoutId = $layout->id())) {
					
					$layout->path = implode('/', $pathComponents);
					
					// Generate path for this layout
					$layoutDirectory = $this->pathForResource('Layouts/'.$layout->path.'/'.filterFileName($layout->name()).'/');
					
					$layout->directory = $layoutDirectory;
					$layout->fileName = 'Layout';
					
					$this->layoutObjects[$layout->id()] = $layout;
					
					$layout->write();
				
				}
				
			}

		}
		
		$layoutCount = count($this->layoutObjects);
		
		doLog('Layouts: '.$layoutCount);
		
	}

	function processValueLists() {
	
		debugLog('Processing value lists');
		
		// Find value list nodes
		$valueLists = $this->q('/'.$this->rootTagName.'[1]/Structure[1]/AddAction[1]/ValueListCatalog[1]/ValueList', 'DTValueList');
		
		$this->valueListObjects = [];

		$valueListsDirectory = $this->pathForResource('Value Lists/');
		$listCount = 0;
		
		foreach ($valueLists as $valueList) {
			
			// Generate path for this value list
			
			$listCount++;
			
			$valueList->directory = $valueListsDirectory;
			$valueList->fileName = filterFileName($valueList->name());
			
			$this->valueListObjects[$valueList->id()] = $valueList;
			
			$valueList->write();
		

		}
		
		doLog('Value lists: '.$listCount);
			
	}
	
	function q($xPath, $class = false) {
		
		if ($class === false) {
			$this->registerNodeClass('DOMElement', 'DTElement');
		} else {
			$this->registerNodeClass('DOMElement', $class);
		}
		
		$this->queryCount++;
		
		$result = $this->xPath->query($xPath);
		
		$this->registerNodeClass('DOMElement', 'DTElement');
		
		return $result;
		
	}
	
	function logMissingDescriptionMethods() {
		
		foreach($this->missingDescriptionMethods as $className => $missingMethods) {
			
			if (count($missingMethods) > 0) {
				
				$allMethods = get_class_methods($className);
				$existingMethods = [];
				foreach ($allMethods as $method) {
					if (startsWith($method, '_') && !startsWith($method, '__')) {
						$existingMethods[] = $method;
					}
				}
				
				$descriptionMethods = array_merge($missingMethods, $existingMethods);
				
				natcasesort($descriptionMethods);
				
				logDivider("Class $className has missing description methods", 'start');
				
				debugLog(implode('(), ', $missingMethods).'()');
				debugLog();
				
				foreach($descriptionMethods as $method) {
					
					if (in_array($method, $existingMethods)) {
						debugLog(phpSource($method, $className));
					} else {
						debugLog("\tfunction $method() {\n\n\t\t\n\n\t}\n");	
					}
					
				}
				
				logDivider("Class $className has missing description methods", 'end');
		
			}
		
		}
		
	}
	
}