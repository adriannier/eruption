<?php
	
class DTParameter extends DTElement {
	
	public $childPaths = [
		'*' => 'DTParameter'
	];
	
/*
	function shouldUnpackChildren() {
		
		return true;
		
	}
	
	function nameForDescription() {
	
		return false;
			
	}
*/
	
	function descriptionMethodName() {
		
		$methodName = '_'.preg_replace("/[^a-zA-Z0-9_]+/", "", str_replace(' ', '_', $this->nodeName));
		// debugLog('descriptionMethodName: '.$this->nodeName.' = '.$methodName);
		return $methodName;
			
	}
	
		function _action() {

		

	}

	function _AllowScreenReader() {

		

	}

	function _Animation() {

		if ($this->name() != 'None') {
			return 'Animation="'.$this->name().'"';
		}
		
		return false;

		
	}

	function _Authentication() {

		

	}

	function _BCC() {

		

	}

	function _Boolean() {
	
		$type = $this->attr('type');
		$value = $this->attr('value');
		
		if (!empty($type)) {
			
			if ($value == 'True') {
				return $type;
			} else {
				return false;
			}
			
		} else if (!empty($this->attr('value'))) {
			
			return $value;
			
		}

	}

	function _Bounds() {

		

	}

	function _Calculation() {

		if (!is_null($text = $this->firstChild('Calculation/Text', 'DTParameter'))) {
			return $text->text();				
		}

	}

	function _CC() {

		

	}

	function _CharacterSet() {

		

	}

	function _Close() {

		

	}

	function _CollectAddresses() {

		

	}

	function _Compress() {

		

	}

	function _Control() {

		

	}

	function _CustomMenuSetReference() {

		

	}

	function _DataSourceReference() {

		

	}

	function _DimParentWindow() {

		

	}

	function _Display() {

		

	}

	function _Document() {

		

	}

	function _Edit() {

		

	}

	function _Email() {

		

	}

	function _EnableCopying() {

		

	}

	function _Encryption() {

		

	}

	function _End() {

		

	}

	function _Excel() {

		

	}

	function _Export() {

		

	}

	function _Field() {

		

	}

	function _FieldReference() {

		$fieldName = $this->name();
		$tableName = $this->attr('tableOccurrence');
		$fieldId = $this->id();
		
		if (!empty($fieldName) && !empty($fieldId) && !empty($tableName)) {
			return "$tableName::$fieldName (id: $fieldId)";
		}

	}

	function _FilePathList() {

		

	}

	function _Filters() {

		

	}

	function _find() {

		$this->addChildDescription('FieldReference', function() {
			
			if (!is_null($fieldRef = $this->firstChild('FieldReference', 'DTParameter'))) {

				return $fieldRef->describe();
							
			}
			
		});

	}

	function _FindRequest() {

		

	}

	function _FindRequestSet() {

		

	}

	function _height() {

		

	}

	function _ImportField() {

		

	}

	function _Include() {

		

	}

	function _Label() {

		

	}

	function _Layout() {

		

	}

	function _LayoutReference() {
		
		return '"'.$this->name().'" (id: '.$this->id().')';
		
	}

	function _LayoutReferenceContainer() {

		$this->unpackChildren = true;
		
		
		
		$this->addHiddenAttribute('value');
		
		$this->addChildDescription('Label', function() {
			
			if (!is_null($label = $this->firstChild('Label', 'DTParameter'))) {

				return $label->describe();
							
			}
			
		});

		$this->addChildDescription('LayoutReference', function() {
			
			if (!is_null($layout = $this->firstChild('LayoutReference', 'DTParameter'))) {

				return $layout->describe();
							
			}
			
		});

	}

	function _left() {

		

	}

	function _List() {

		$this->unpackChildren = true;
		
		$this->addHiddenAttribute('name');
		$this->addHiddenAttribute('value');
		
		$this->addChildDescription('ScriptReference', function() {
			
			if (!is_null($scriptRef = $this->firstChild('ScriptReference', 'DTParameter'))) {

				return $scriptRef->describe();
							
			}
			
		});

	}

	function _Magnification() {

		

	}

	function _Map() {

		

	}

	function _Maximize() {

		

	}

	function _MenuBar() {

		

	}

	function _Message() {

		

	}

	function _Minimize() {

		

	}

	function _Multiple() {

		

	}

	function _Name() {

		$this->useEqualSymbolWithOneChild = true;

	}

	function _Open() {

		

	}

	function _Options() {
	
		$this->hideNameInDescriptionWithoutChildren = true;
		
		if ($this->childCount() == 0 && !empty($this->type())) {

			return $this->type().': '.$this->text();
			
		} else if ($this->type() == 'Calculation' && $this->childNames() == [$this->type()]) {
			
			if (!is_null($text = $this->firstChild('Calculation/Calculation/Text', 'DTParameter'))) {

				return $text->text();
							
			} 
		}
		

	}

	function _Order() {

		

	}

	function _Orientation() {

		

	}

	function _Pages() {

		

	}

	function _PageSetup() {

		

	}

	function _Parameter() {

		$this->unpackChildren = true;

		$this->addChildDescription('Calculation', function() {
			
			if (!is_null($text = $this->firstChild('Calculation/Calculation/Text', 'DTParameter'))) {

				return $text->text();
							
			}
			
		});

	}

	function _Password() {

		

	}

	function _Port() {

		

	}

	function _PrimaryField() {

		

	}

	function _Print() {

		

	}

	function _Rename() {

		

	}

	function _repetition() {

		$rep = $this->attr('value');
		
		if (!empty($rep)) {
			if ($rep == '1') {
				return false;
			} else {
				return 'Repetition='.$rep;
			}
		} 
		
		return false;
	}

	function _ReplyTo() {

		

	}

	function _Resize() {

		

	}

	function _Restore() {

		

	}

	function _scale() {

		

	}

	function _ScriptReference() {

		$scriptName = $this->name();
		$scriptId = $this->id();
		$repetition = $this->firstChild('repetition');
		
		if (!empty($scriptName) && !empty($scriptId)) {
			return "\"$scriptName\" (id: $scriptId)";
		}
		
	}

	function _Security() {

		

	}

	function _Send() {

		

	}

	function _Server() {

		

	}

	function _show() {

		

	}

	function _size() {

		

	}

	function _SMTP() {

		

	}

	function _Sort() {

		

	}

	function _SortList() {

		

	}

	function _SortSpecification() {

		

	}

	function _Source() {

		

	}

	function _Start() {

		

	}

	function _Storage() {

		

	}

	function _Subject() {

		

	}

	function _TableOccurrenceReference() {

		

	}

	function _Target() {

		

	}

	function _Title() {

		

	}

	function _To() {

		

	}

	function _Toolbar() {

		

	}

	function _top() {

		

	}

	function _URL() {

		

	}

	function _UserName() {

		

	}

	function _value() {

		

	}

	function _ValueListReference() {

		

	}

	function _Variable() {

		

	}

	function _View() {

		

	}

	function _Voice() {

		

	}

	function _width() {

		

	}

	function _WindowReference() {

		$this->addChildDescription('Select', function() {
			
			if (!is_null($select = $this->firstChild('Select'))) {
			
				$selectKind = $select->attr('kind');
				
				if ($selectKind == 'Calculated') {
					
					if ($text = $select->firstChild('Name/Calculation/Calculation/Text')) {
						
						return $text->text();
						
					}
					
				} else if ($selectKind == 'current') {
					
					return 'Current Window';
					
				} else {
					
					$this->warn("Unknown selection kind '$selectKind'");
					
				}
				
			}
			
		});
		
		$this->addChildDescription('Style', function() {
			
			if (!is_null($style = $this->firstChild('Style'))) {
			
				$styleName = $style->attr('name');
				
				if (!empty($styleName)) {
					
					return "Style: $styleName";
					
				} else {
					
					$this->warn("Empty style name");
					
				}
				
			}
			
		});
		
		$this->addChildDescription('Name', function() {
			
			if (!is_null($name = $this->firstChild('Name/Calculation/Calculation/Text'))) {

				return "Name: ".$name->text();
							
			}
			
		});
		
		$this->addChildDescription('LayoutReferenceContainer', function() {
			
			if (!is_null($layout = $this->firstChild('LayoutReferenceContainer/LayoutReference'))) {

				return "Layout: ".$layout->name();
							
			}
			
		});
		
		$this->addChildDescription('Options', function() {
			
			if (!is_null($options = $this->firstChild('Options'))) {

				if ($options->attr('value') == '3221225476') {
					return false;
				}
							
			}
			
		});

	}	
}