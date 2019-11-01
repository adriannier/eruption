<?php
	
class DTElement extends DOMElement {
	
	public $index;
	public $siblingCount;
	public $parentElement;
	
	public $id;
	public $name;
	public $type;

	public $ignoredNodeClasses = ['DOMText', 'DOMCdataSection'];
	public $hiddenAttributes = ['membercount'];
	
	public $childPaths = [
		'*' => 'DTElement'
	];
	
	public $unpackChildren = false;
	public $unpackOnlyChild = false;

	public $hideNameInDescription = false;
	public $useEqualSymbolWithOneChild = false;
	public $hideNameInDescriptionWithoutChildren = false;
	
	public $preparedChildDescriptions = [];
	
	public $logInput = false;
	public $logOutput = false;
	
	private $descriptions;
	private $description;
	
	public $childNames;
	public $hasChildren;
	public $childCount;
	public $generation = 0;
	public $emptyDescriptionMethods = [];	

	public $directory;
	public $fileName;
	public $fileExtension = '.txt';
	
	function paddedSiblingNumber($indexOverride = false) {
		
		if ($indexOverride !== false) {
			$index = $indexOverride;
		} else {
			$index = $this->index + 1;
		}
		
		return str_pad((string) $index, strlen((string) $this->siblingCount), '0', STR_PAD_LEFT);
	
	}
	
	function attr($name) {
		
		return $this->getAttribute($name);
		
	}
	
	function addHiddenAttribute($name) {
		$this->hiddenAttributes[] = $name;
	}
	
	function id() {
		
		if (!isset($this->id)) {
			$this->id = $this->getAttribute('id');			
		}
		
		return $this->id;
		
	}	
	
	function name() {
		
		if (!isset($this->name)) {
			$this->name = $this->getAttribute('name');			
		}
		
		return $this->name;
		
	}
	
	function elementWithName(&$elements, $name) {
		
		foreach ($elements as $element) {
			
			if ($element->name() == $name) {
				return $element;
			}
			
		}
		
	}
	
	function type() {
		
		if (!isset($this->type)) {
			$this->type = $this->getAttribute('type');			
		}
		
		return $this->type;
		
	}
	
	function xml($node = 'NoNodeSpecified') {
		
		if ($node == 'NoNodeSpecified') {
			$node = $this;
		}
		
		$rawXML = $this->ownerDocument->saveXML($node);
		$formattedXML = formatXML($rawXML);
		$shortenedXML = preg_replace('/^.+\n/', '', $formattedXML);
		$trimmedXML = trim($shortenedXML);
		
		return $trimmedXML;
		
	}
	
	function descriptionMethodName() {
		
		return false;
		 
	}
	
	function preProcess() {
		
		
	}
	
	function process() {
		
		// Make sure the node is something we are interested in
		if (!in_array(get_class($this), $this->ignoredNodeClasses)) {
			
			// Get the method name to describe this node
			$methodName = $this->descriptionMethodName();
					
			if (!empty($methodName)) {
				
				if (method_exists($this, $methodName)) {
				
					// Use the custom method
					$this->debugLog('Describing with custom method '.$methodName);
					$description = $this->{$methodName}();
														
				} else {

					if (defined('DEBUG') && DEBUG === true) {
						
						// Add to list of missing methods
						
						$className = get_class($this);
						if (!isset($this->ownerDocument->missingDescriptionMethods[$className])) {
							$this->ownerDocument->missingDescriptionMethods[$className] = [];
						}
						
						if (!in_array($methodName, $this->ownerDocument->missingDescriptionMethods[$className])) {
							
							$this->ownerDocument->missingDescriptionMethods[$className][] = $methodName;
							
						}
					
					}
					
				}
					
			}
			
			if (!isset($description) || is_null($description)) {
				
				if (defined('DEBUG') && DEBUG === true) {
			
					if (isset($this->preparedChildDescriptions)) {
						
						$descriptionNames = array_keys($this->preparedChildDescriptions);
						if (count($descriptionNames) > 0) {
							$this->debugLog('Described children: '.implode(', ', $descriptionNames));							
						}
						
					}
				
				}
				
				// Custom method returned null; use generic method
				$this->debugLog('Describing with generic method');
				$description = $this->description();
			
			}
	
		}
		
		return $description;

	}
	
	function postProcess($description) {
		
		return $description;
		
	}
		
	function describe() {
		
		$this->preProcess();
		
		$description = $this->process();
						
		$description = $this->postProcess($description);
		
		if ($this->logInput === true) {
			doLog("Input: ".$this->xml());
		} else if ($this->logInput === 'parent') {
			doLog("Input: ".$this->xml($this->parentNode));			
		} else if ($this->logInput === 'grandparent') {
			doLog("Input: ".$this->xml($this->parentNode->parentNode));			
		}
		
		if ($this->logOutput === true) {
			doLog("Output: ".$description."\n");				
		}
			
		return $description;
			
	}
	
	function shouldUnpackChildren() {
		
		if ($this->childCount() == 1 && $this->unpackOnlyChild === true) {
			return true;
		} else {
			return $this->unpackChildren;			
		}

	}
	
	function description() {
		
		$this->descriptions = [];
				
		// Add descriptions of this node's attributes
		$this->describeAttributes();	
		
		// Add descriptions for this node's children
		$this->describeChildren();
		
		// Get the content of this node
		if (!$this->hasChildren()) {
			$this->describeContent();
		}
				
		if (empty($this->descriptions)) {
			
			if ($this->hideNameInDescriptionWithoutChildren === true) {
				return false;
			} else {
				return $this->nameForDescription();
			}
			
			
		} else {

			if ($this->shouldUnpackChildren()) {
				
				$description = implode(' ; ', $this->descriptions);
				
			} else {
				
				if (!$this->hideNameInDescription) {
					
					if (count($this->descriptions) == 1 && $this->useEqualSymbolWithOneChild) {
						$description = $this->nameForDescription().'='.$this->descriptions[0];
					} else {
						$description = $this->nameForDescription().' [ '.implode(' ; ', $this->descriptions).' ]';	
					}
					
				} else {
					
					$description = implode(' ; ', $this->descriptions);
					
				}
				
			}
			
		}
		
		return $description;
		
	}
	
	function nameForDescription() {
		
		if (!$this->hideNameInDescription) {
			return $this->nodeName;
		} else {
			return false;
		}
		
	}
	
	function describeAttributes() {
		
		if ($this->hasAttributes()) {
			
			foreach($this->attributes as $attr) {
				
				$name = $attr->nodeName;
				
				if (!in_array($name, $this->hiddenAttributes)) {
					
					$value = $attr->nodeValue;
					
					array_push($this->descriptions, $name.'="'.$value.'"');
					
				}
				
			}
			
		}
		
	}
	
	function childPaths() {
		
		return $this->childPaths;
		
	}
	

	function childrenAtPath($childPath, $childClass = 'NoClassSpecified') {
		
		if ($childClass == 'NoClassSpecified') {
			$childClass = $this->classForChild($childPath);
		}
		
		$children = [];
		
		if ($this->hasChildNodes()) {
			
			$this->ownerDocument->registerNodeClass('DOMElement', $childClass);
			
			if ($childPath == '*') {
				
				foreach ($this->childNodes as $child) {
					if (!in_array(get_class($child), $this->ignoredNodeClasses)) {
						$child->generation = $this->generation + 1;
						array_push($children, $child);
					}
				}

			} else if (strpos($childPath, '[') === false) {
			
				foreach ($this->childNodes as $child) {
					if ($childPath == $child->nodeName) {
						$child->generation = $this->generation + 1;
						array_push($children, $child);
					}
				}
							
			} else {
				
				$searchResults = $this->ownerDocument->q($this->getNodePath().'/'.$childPath, $childClass);
				
				foreach ($searchResults as $searchResult) {

					if (!in_array(get_class($searchResult), $this->ignoredNodeClasses)) {
						$searchResult->generation = $this->generation + 1;
						array_push($children, $searchResult);
					}
					
				}
				
			}
		
		}
		
		return $children;
		
	}
	
	function hasChildren() {
		
		if (!isset($this->hasChildren)) {
			
			$this->hasChildren = false;
				
			if ($this->hasChildNodes()) {
				
				foreach($this->childPaths() as $childPath => $childClass) {
					
					foreach ($this->childrenAtPath($childPath, $childClass) as $child) {
					
						if (!in_array(get_class($child), $this->ignoredNodeClasses)) {
							
							$this->hasChildren = true;
							return true;
							
						}
						
					}
						
				}
			
			}

		}
		
		return $this->hasChildren;
		
	}
	
	function childCount() {
	
		if (!isset($this->childCount)) {
			
			$childCount = 0;
			
			if ($this->hasChildren()) {
			
				foreach($this->childPaths() as $childPath => $childClass) {
					
					foreach ($this->childrenAtPath($childPath, $childClass) as $child) {
					
						if (!in_array(get_class($child), $this->ignoredNodeClasses)) {
							
							$childCount++;
							
						}
						
					}
						
				}
			
			}
			
			$this->childCount = $childCount;

		} 
		
		return $this->childCount;
			
	}
	
	function childNames() {
		
		if (!isset($this->childNames)) {
			
			$names = [];
			
			if ($this->hasChildren()) {
			
				foreach($this->childPaths() as $childPath => $childClass) {
					
					foreach ($this->childrenAtPath($childPath, $childClass) as $child) {
					
						if (!in_array(get_class($child), $this->ignoredNodeClasses)) {
							
							array_push($names, $child->nodeName);
							
						}
						
					}
						
				}
			
			}
			
			$this->childNames = $names;
			
			if (!isset($this->childCount)) {
				$this->childCount = count($names);
			}

		} 
		
		return $this->childNames;
	
	}
	
	function describeChildren() {
		
		if ($this->hasChildren()) {
		
			// Get the prepared descriptions for all children
			
			$childrenWithPreparedDescription = array_keys($this->preparedChildDescriptions);
			
			foreach($childrenWithPreparedDescription as $childName) {
				
				$childCount = count($this->childrenAtPath($childName, 'DTElement'));
				
				if ($childCount == 1) {
					
					if (!empty($this->preparedChildDescriptions[$childName])) {

/*
						if (gettype($this->preparedChildDescriptions[$childName]) == 'object' && get_class($this->preparedChildDescriptions[$childName]) == 'Closure') {
							$childDescription = $this->preparedChildDescriptions[$childName]();
						} else {
*/
							$childDescription = $this->preparedChildDescriptions[$childName];
// 						}
						
						array_push($this->descriptions, $childDescription);
					}
					
				} else if ($childCount == 0) {
					
					// Do nothing 
					
				} else {
			
					// More than one child element has been found by that name
					// Delete the prepared description, so a new one can be generated
					unset($this->preparedChildDescriptions[$childName]);
					
					$this->warn("Encountered more than one child with name '$childName'");
					
				}

				
			}
			
			foreach($this->childPaths() as $childPath => $childClass) {
			
				$children = $this->childrenAtPath($childPath, $childClass);
				
				$this->debugLog('Found children of type '.$childClass.': '.count($children));
				
				for ($i = 0; $i < count($children); $i++) {
				
					$child = $children[$i];

					if (!in_array(get_class($child), $this->ignoredNodeClasses)) {

						if (isset($this->preparedChildDescriptions[$child->nodeName])) {

							// Do nothing
							$this->debugLog('Description for child '.$child->nodeName.' has already been prepared');
							
							
						} else {

							$description = $child->describe();

							if (!empty($description)) {
								
								$this->debugLog('Adding description: '.$description);
								
								array_push($this->descriptions, $description);
							}
								
						}				
						
					}
					
				}
				
			}
		
		}
	
	}
	
	function addChildDescription($childName, $func) {
		
		$this->preparedChildDescriptions[$childName] = $func();
		
	}	
	
	function describeContent() {
		
		if (($text = $this->text()) != '') {
			array_push($this->descriptions, $text);	
		}
		
	}

	function q($xPath, $childClass = 'NoClassSpecified') {

		if ($childClass == 'NoClassSpecified') {
			$childClass = $this->classForChild($xPath);
		}
		
		if (!startsWith($xPath, '/')) {
		
			// Make relative xPath absolute
			$xPath = $this->getNodePath().'/'.$xPath;
			
		}
	
		return $this->ownerDocument->q($xPath, $childClass);
				
	}
	
	function classForChild($childName) {
		
		if (isset($this->childPaths)) {
				
			if (isset($this->childPaths[$childName])) {
				
				return $this->childPaths[$childName];
				
			} else if (isset($this->childPaths['*'])) {
				
				return $this->childPaths['*'];
								
			}
					
		}	
		
		return get_class($this);
	}
	
	function firstChild($childName, $childClass = 'NoClassSpecified') {
		
		if ($childClass == 'NoClassSpecified') {
			
			$childClass = $this->classForChild($childName);
			
		}
		
		$this->ownerDocument->registerNodeClass('DOMElement', $childClass);
		
		if (strpos($childName, '/') !== false) {
	
			$children = explode('/', $childName);
			$e = $this;
			
			foreach ($children as $childName) {
				
				$e = $e->firstChild($childName);
				
				if (is_null($e)) {
					return;
				}
			}
			
			return $e;
			
		} else {

			foreach ($this->childNodes as $child) {
				if ($childName == $child->nodeName) {
					return $child;
				}
			}
			
			return;
			
		}
		
	}
	
	function text() {
		
		return trim(collapseWhiteSpace($this->textContent));
	}
	
	function directory() {
	
		return $this->directory;
			
	}
	
	function fileName() {
		
		if (isset($this->fileName)) {
			return $this->fileName;	
		} else {
			return filterFileName($this->name());
		}
		
	}
	
	function fileExtension() {
		
		return $this->fileExtension;
		
	}
	
	function filePath($counter = 1) {

		$directory = $this->directory();
		$fileName = $this->fileName();
		$fileExtension = $this->fileExtension();

		if (!isset($directory)) {
			throwError('Directory not set');
		}
		
		if (!isset($fileName)) {
			throwError('File name not set');
		}
		
		if (!isset($fileExtension)) {
			throwError('File extension not set');
		}
		
		if ($counter == 1) {
			return $directory.filterFileName($fileName).$fileExtension;
		} else {
			return $directory.filterFileName($fileName.'_'.$counter).$fileExtension;
		}

	}
	
	function writeAdditionalResources() {
		
		/* Override in subclass */
		
	}
	
	function writeData($data, $overridePath = false) {
		
		$directory = $this->directory();
		
		if (!isset($directory)) {
			throwError('Directory not set');
		}

		checkDirectory($directory);

		$counter = 1;
		while (is_file($this->filePath($counter))) {
			$counter++;
		}
		
		if ($overridePath === false) {
			$filePath = $this->filePath($counter);
		} else {
			$filePath = $overridePath;			
		}

		file_put_contents($filePath, $data);
		
	}
	
	function write($overridePath = false) {
		
		$this->writeData($this->describe(), $overridePath);
		$this->writeAdditionalResources();
			
	}
	
	function writeXML($overridePath = false) {
		
		$this->writeData($this->xml(), $overridePath);
		$this->writeAdditionalResources();
		
	}
	
	function echoXML() {
	
		$xml = collapseWhiteSpace($this->xml());
		$xml = str_replace('> <', '><', $xml);
		echo($xml."\n");
			
	}
	
	function dNode($stackOffset = 0) {
		
		// Dump the current node's XML
		
		d($this->xml(), 'Dumping node '.$this->getNodePath(), $stackOffset + 1);
		
	}
	
	function ddNode($stackOffset = 0) {
		
		// Dump the current node's XML and exit

		dd($this->xml(), 'Dumping node', $stackOffset + 1);
		
	}
	
	function dParent($stackOffset = 0) {
		
		// Dump the current node's XML
		
		d($this->xml($this->parentNode), 'Dumping parent node', $stackOffset + 1);
		
	}
	
	function ddParent($stackOffset = 0) {
		
		// Dump the current parent's XML and exit
		
		dd($this->xml($this->parentNode), 'Dumping parent node', $stackOffset + 1);
		
	}
	
	function dGrandparent($stackOffset = 0) {
		
		// Dump the current grandparent's XML
		
		d($this->xml($this->parentNode->parentNode), 'Dumping grand parent node', $stackOffset + 1);
		
	}
	
	function ddGrandparent($stackOffset = 0) {
		
		// Dump the current grandparent's XML and exit
		
		dd($this->xml($this->parentNode->parentNode), 'Dumping grand parent node', $stackOffset + 1);
		
	}
	
	function warn($msg, $stackOffset = 0) {
		
		doLog('[ WARNING! ] '.$msg.' in '.caller($stackOffset).' while processing '.$this->getNodePath());
		$this->dNode(1);
		
	}
	
	function debugLog($var) {
	
		if (defined('DEBUG') && DEBUG === true) {
			
			debugLog($this->logDescription().': '.varDescription($var));
			
		}
	
	}
			
	function logDescription() {
		
		$description = $this->nodeName;
		
		$attributes = [];
		
		if (!empty($this->name())) {
			$attributes[] = 'name: '.$this->name();
		}
		
		if (!empty($this->id())) {
			$attributes[] = 'id: '.$this->id();
		}
		
		if (!empty($this->type())) {
			$attributes[] = 'type: '.$this->type();
		}
		
		$attributes[] = 'class: '.get_class($this);
		
		$indentation = str_pad('', $this->generation * 4, ' ');
		
		return $indentation.$description.(count($attributes) > 0 ? ' ('.implode(', ', $attributes).')' : '');
		
	}

	
}