<?php
	
class DTParameterContainer extends DTElement {
	
	public $childPaths = [
		'*' => 'DTParameter'
	];
	
	function preProcess() {
		
		if (empty($this->type())) {
			$this->warn('Parameter container without type');
		}
		
		$this->addHiddenAttribute('type');
					
	}
	
	function descriptionMethodName() {
		
		$methodName = '_'.preg_replace("/[^a-zA-Z0-9_]+/", "", str_replace(' ', '_', $this->type()));
		return $methodName;
			
	}
	
	function shouldUnpackChildren() {
		
		return true;
		
	}
	
		function _action() {

		

	}

	function _Animation() {

		$this->hideNameInDescriptionWithoutChildren = true;

	}

	function _Boolean() {

		

	}

	function _Button1() {

		

	}

	function _Button2() {

		

	}

	function _Button3() {

		

	}

	function _Calculation() {

		

	}

	function _CustomMenuSet() {

		

	}

	function _DataSourceReference() {

		

	}

	function _Email() {

		

	}

	function _Export() {

		

	}

	function _Field1() {

		

	}

	function _Field2() {

		

	}

	function _Field3() {

		

	}

	function _FieldReference() {

		

	}

	function _FilePathList() {

		

	}

	function _FindRequest() {

		

	}

	function _ImportField() {

		

	}

	function _LayoutReferenceContainer() {

		

	}

	function _List() {

		

	}

	function _Location() {

		

	}

	function _Message() {

		

	}

	function _Name() {

		

	}

	function _Object() {

		

	}

	function _Options() {

		

	}

	function _PageSetup() {

		

	}

	function _Parameter() {

		

	}

	function _Password() {

		

	}

	function _Portal() {

		

	}

	function _Presentation() {

		

	}

	function _Print() {

		

	}

	function _Records() {

		

	}

	function _Related() {

		

	}

	function _replace() {

		

	}

	function _Restore() {

		

	}

	function _ScriptReference() {

		

	}

	function _Select() {

		

	}

	function _SortSpecification() {

		

	}

	function _Source() {

		

	}

	function _Target() {

		

	}

	function _Title() {

		

	}

	function _URL() {

		

	}

	function _Variable() {

		$this->addChildDescription('Name', function() {
			
			if (!is_null($name = $this->firstChild('Name'))) {
			
				$value = $name->attr('value');
				
				if (!empty($value)) {
					
					return "$value";
					
				} else {
					
					$this->warn("Empty variable name");
					
				}
				
			}
			
		});
		
		$this->addChildDescription('value', function() {
			
			if (!is_null($text = $this->firstChild('value/Calculation/Calculation/Text'))) {
			
				return 'Value: '.$text->text();
							
			}
			
		});

	}

	function _Voice() {

		

	}

	function _Wait() {

		

	}

	function _WindowReference() {

		

	}
	
}