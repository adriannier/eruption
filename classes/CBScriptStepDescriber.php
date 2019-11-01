<?php

class CBScriptStepDescriber {
	
	public $element;
	public $simpleXML;
	
	private $formattingData;
	private $currenPath;
	
	// Specify the description style
	public $descriptionStyle = 'FileMakerDescriptionStyle';
	
	// Specify default style settings
	public $defaultStyleSettings = 	[
		
		'JavaScriptDescriptionStyle' => [
			
				'globalSub' => [ 
					'≠' => '!='
				]	
				
		]
		
	];
	
	public $missingScriptStepsDescriptions = [];
	
	function defaultDescription($formattingData = false) {

		if ($formattingData !== false) {
			
			$this->formattingData = $formattingData;
						
		} else {
			
			$this->formattingData = array();				

		}
		
		if ($this->descriptionStyle == 'JavaScriptDescriptionStyle') {
			
			$this->addDefaultFormattingStyle($formattingData, $this->descriptionStyle, 'emptyDescription', '()');
			$this->addDefaultFormattingStyle($formattingData, $this->descriptionStyle, 'nameDescriptionSeparator', '');
			$this->addDefaultFormattingStyle($formattingData, $this->descriptionStyle, 'descriptionSeparator', ', ');

		}
		
		$str = $this->descriptionForStep();
		
		return $this->postProcessDescription($str);
		
	}
	
	function addDefaultFormattingStyle(&$formattingData, $style, $key, $value) {

		$stylesKey = 'styles';
		
		if (isset($formattingData[$stylesKey])) {
			
			if (isset($formattingData[$stylesKey][$style])) {
				
				if (!isset($formattingData[$stylesKey][$style][$key])) {
					
					$formattingData[$stylesKey][$style][$key] = $value;
					
				}
				
			} else {
				
				$formattingData[$stylesKey][$style] = [ $key => $value ];
				
			}
			
		} else {
			
			$formattingData[$stylesKey] = [ $style => [ $key => $value ] ];
			
		}

	}
	
	function postProcessDescription($str) {
		
		foreach($this->styledValueForKey('postSub', []) as $search => $replace) {
			$str = str_replace($search, $replace, $str);
		}


		foreach($this->styledValueForKey('postReplace', []) as $search => $replace) {
			if (strcmp($str, $search)) { $str = $replace; }
		}

		foreach($this->styledValueForKey('postRegex', []) as $search => $replace) {
			$str = preg_replace($search, $replace, $str);
		}
		
		if (isset($this->defaultStyleSettings[$this->descriptionStyle])) {
			
			if (isset($this->defaultStyleSettings[$this->descriptionStyle]['globalSub'])) {				
				foreach($this->defaultStyleSettings[$this->descriptionStyle]['globalSub'] as $search => $replace) {
					$str = str_replace($search, $replace, $str);
				}
			}

			if (isset($this->defaultStyleSettings[$this->descriptionStyle]['globalReplace'])) {				
				foreach($this->defaultStyleSettings[$this->descriptionStyle]['globalReplace'] as $search => $replace) {
					if (strcmp($str, $search)) { $str = $replace; }
				}
			}

			if (isset($this->defaultStyleSettings[$this->descriptionStyle]['globalRegex'])) {				
				foreach($this->defaultStyleSettings[$this->descriptionStyle]['globalRegex'] as $search => $replace) {
					$str = preg_replace($search, $replace, $str);
				}
			}
			
		}
		
		if ($this->simpleXML['enable'] == 'False') {
			$str = '// '.$str;
		}
		
		return $str;
		
	}
	
	function valueAtPath($path) {
		
		$pathComponents = explode('/', $path);
		array_shift($pathComponents);
		
		$obj = $this->simpleXML;
		foreach($pathComponents as $pathComponent) {
			
			$pos1 = strpos($pathComponent, '[\'');
			
			if ($pos1 !== false) {
				$pos2 = strpos($pathComponent, '\']');
				
				$key = substr($pathComponent, $pos1 + 2, ($pos2 - $pos1) - 2);
				$pathComponent = substr($pathComponent, 0, strpos($pathComponent, '['));
			
			} else {
				$key = false;
			}

			if ($key === false) {
				$obj = $obj->{$pathComponent};
			} else {
				$obj = $obj->{$pathComponent}[$key];				
			}
			
			if (is_null($obj)) {
				break;
			}
		}
		
		if (!is_null($obj)) {
			return collapseWhiteSpace((string) $obj);
		} else {
			return false;
		}
		
	}
	
	function descriptionForStep() {
		
		$this->currentPath = '/';
		
		if (isset($this->formattingData['name'])) {
			$stepName = $this->formattingData['name'];
		} else {
			$stepName = $this->simpleXML['name'];
		}
		
		$formattedDescriptions = array();
		
		if (isset($this->formattingData['parts'])) {

			$partValues = array();
			foreach($this->formattingData['parts'] as $part) {
				array_push($partValues, $this->valueAtPath($part));
			}
			
			// Get the format string
			$format = $this->styledValueForKey('format', false);
			
			if ($format !== false) {
				
				// Replace variables in format string
				for ($i = 0 ; $i < count($partValues) ; $i++) {
					if ($partValues[$i] !== false) {
						$format = str_replace('$'.$i, $partValues[$i], $format);						
					}
				}
				
				// Format substitutions
				foreach($this->styledValueForKey('formatSubs', []) as $search => $replace) {
					$format = str_replace($search, $replace, $format);
				}
				
				// Format replacements
				foreach($this->styledValueForKey('formatReplace', []) as $search => $replace) {
					if (strcmp($format, $search)) {
						$format = $replace;
					}
				}

				// Format Regexes
				foreach($this->styledValueForKey('formatRegex', []) as $search => $replace) {
					$format = preg_replace($search, $replace, $format);
				}
				
				if ($format != '') {
					array_push($formattedDescriptions, $format);
				}
				
			}
		}
		
	
		$attributes = array();
		
		foreach($this->simpleXML->attributes() as $a => $b) {
			
			if ($a != 'step_number' && $a != 'index' && $a != 'enable' && $a != 'id' && $a != 'name') {
				
				$this->currentPath = '/['.$a.']';
				array_push($attributes, $a.'="'.$b.'"');				
				
			}
		}
		
		$this->currentPath = '';
		
		
		$childDescriptions = array();
		
		foreach($this->simpleXML->children() as $child) {
			
			$childDescription = $this->descriptionForElement($child);
			if ($childDescription != '') {
				array_push($childDescriptions, $childDescription);
			}
			
		}
		
		
		
		$descriptions = array_merge($formattedDescriptions, $attributes, $childDescriptions);
		$description = implode($this->styledValueForKey('descriptionSeparator', ' ; '), $descriptions);
				
		return $this->styledValueForKey('prefix').$this->styledNameAndDescription($stepName, $description).$this->styledValueForKey('suffix');
		
		
	}
		
	function styledNameAndDescription($stepName, $description) {
		
		$styledName = $this->styledValueForKey('name', $stepName);

		if ($this->descriptionStyle == 'JavaScriptDescriptionStyle') {

			$styledDescription = str_replace('FMKeepSpace', ' ', removeWhiteSpace(str_replace('\ ', 'FMKeepSpace', $description)));
					
			if ($styledName != '') {
		
				
				
				$styledName = lcfirst(str_replace(' ', '', ucwords($styledName)));
				
				if ($styledDescription != '') {
					return $styledName.$this->styledValueForKey('nameDescriptionSeparator').'('.$styledDescription.')';
				} else {
					return $styledName.$this->styledValueForKey('nameDescriptionSeparator').$this->styledValueForKey('emptyDescription');
				}
				
			} else {
				
				return $styledDescription;
				
			}

		} else {
			
			$styledDescription = '[ '.collapseWhiteSpace($description).' ]';
			



			if ($styledName == '' && $styledDescription != '[  ]') {
				
				return $this->styledValueForKey('emptyStepName').$styledDescription;

			} else if ($styledName != '' && $styledDescription != '[  ]') {

				return $styledName.$this->styledValueForKey('nameDescriptionSeparator', ' ').$styledDescription;
			
			} else  {
				
				return $styledName.$this->styledValueForKey('emptyDescription');	
			}
			
		}
	}
	
	function styledValueForKey($key, $default = '') {
	
		if (isset($this->descriptionStyle) && $this->descriptionStyle !== false) {
		
			if (isset($this->formattingData['styles']) && isset($this->formattingData['styles'][$this->descriptionStyle])) {

				$styleInfo = $this->formattingData['styles'][$this->descriptionStyle];
				
				if (isset($styleInfo[$key])) { 
					return $suffix = $styleInfo[$key];
					
				}

			}
			
		}
		
		if (isset($this->formattingData[$key])) {
			
			return $this->formattingData[$key];
			
		}
		
		return $default;
		
	}	
	
	function descriptionForElement($e) {
		
		$previousPath = $this->currentPath;
		$this->currentPath .= '/'.$e->getName();
		
		$descriptions = array();
		
		if (!isset($this->formattingData['parts']) || (isset($this->formattingData['parts']) && !in_array($this->currentPath, $this->formattingData['parts']))) {
				
			$value = trim(collapseWhiteSpace((string) $e));
			
			if ($value != '') {
				array_push($descriptions, $value);
			}
				
		}
		
		$attr = trim($this->attributesToText($e));
		if ($attr != '') {
			array_push($descriptions, $attr);
		}
		
		foreach($e->children() as $child) {
			
			$childDescription = trim($this->descriptionForElement($child));
			if ($childDescription != '') {
				array_push($descriptions, $childDescription);
			}
			
		}
		
		$description = trim(implode(' ; ', $descriptions));
		
		$path = $this->currentPath;
		$this->currentPath = $previousPath;
		
		if ($description != '') {
			
			$elementName =  $this->tranformElementName($e->getName());
						
			if ($description == 'state="False"') {
				

				if ($elementName == 'With dialog') {
					return $elementName; // *With dialog* is flipped
				} else {
					return $elementName.': Off';
				}

			} else if ($description == 'state="True"') {

				
				
				if ($elementName == 'With dialog') {
					return $elementName.': Off'; // *With dialog* is flipped
				} else {
					return $elementName.': On';
				}
				
			} else {
				
				return $elementName.' [ '.$description.' ]';
			}
			
			
		} else {
			return '';
		}

	}
	
	function typeDependendDescription($element, $path, $description) {
		
		if (isset($this->formattingData['types']) && in_array($path, array_keys($this->formattingData['types']))) {
				
			$expectedType = $this->formattingData['types'][$path];
			
			if ($expectedType == 'boolean') {
				
				if ($description == 'state="False"') {
					return $this->tranformElementName($element->getName()).': Off';
				} else if ($description == 'state="True"') {
					return $this->tranformElementName($element->getName()).': On';
				}

			} else if ($expectedType == 'boolean/flip') {
				
				if ($description == 'state="False"') {
					return $this->tranformElementName($element->getName()).': On';
				} else if ($description == 'state="True"') {
					return $this->tranformElementName($element->getName()).': Off';

				}
				
			}
			
		}
		
	}
	
	function tranformElementName($name) {
		
		if ($name == 'NoInteract') {
			
			return 'With dialog';
			
		} else if (isset($this->formattingData['key_transforms']) && in_array($name, array_keys($this->formattingData['key_transforms']))) {
			
			return $this->formattingData['key_transforms'][$name];
			
		} else {
			
			return $name;
			
		}
		
	}
	
	function attributesToText($e) {

		$previousPath = $this->currentPath;

		$attributes = array();
		foreach($e->attributes() as $a => $b) {
			
			$this->currentPath = $previousPath.'[\''.$a.'\']';
			
			if (!isset($this->formattingData['parts']) || (isset($this->formattingData['parts']) && !in_array($this->currentPath, $this->formattingData['parts']))) {
				
				array_push($attributes, $a.'="'.$b.'"');
			
			}
		}
		
		$this->currentPath = $previousPath;
		
		return implode(' ', $attributes);
		
		
	}	
	
	function field() {
		return '"'.$this->fieldTable().'::'.$this->fieldName().'"';	
	}	
	
	function fieldTable() {
		return (string) $this->simpleXML->Field['table'];
	}
	
	function fieldName() {
		return (string) $this->simpleXML->Field['name'];
	}
	
	function calculation() {
		return collapseWhiteSpace((string) $this->simpleXML->Calculation);
	}
	
	function valueCalculation() {
		return collapseWhiteSpace((string) $this->simpleXML->Value->Calculation);	
	}
	
	function scriptName() {
		return (string) $this->simpleXML->Script['name'];
	}
	
	function text() {
		return (string) $this->simpleXML->Text;
	}
	
	function name() {
		return (string) $this->simpleXML->Name;
	}
	
	function collapseWhiteSpace($str) {
		
		$str = str_replace(chr(13), '¶', $str);
		$str = str_replace(chr(10), ' ', $str);
		$str = str_replace(chr(9), ' ', $str);
		
		while (strpos($str, '  ') !== false) {
			$str = str_replace('  ', ' ', $str);
		}
		
		return $str;
	}
	
	function removeWhiteSpace($str) {
		
		$str = collapseWhiteSpace($str);
		$str = str_replace(' ', '', $str);
		
		return $str;
	}

	// !Control script steps
	
	function _Allow_User_Abort() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/Set['state']"
								],
						
			'format' 		=> 	'$0',
			
			'postSub'		=>	[
									'[ False ]' => '[ Off ]',
									'[ True ]' => '[ On ]'
								]
		]);
	
	}
	
	function _Configure_Region_Monitor_Script() {
	
		return $this->defaultDescription();
	
	}
	
	function _Else() {
	
		return $this->defaultDescription();
	
	}
	
	function _Else_If() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/Calculation"
								],
						
			'format' 		=> 	'$0'
		]);
	
	}
	
	function _End_If() {
	
		return $this->defaultDescription([
			
			'emptyDescription' => '',
			
			'styles'		=> 	[
									'JavaScriptDescriptionStyle' => 	[
															'name' => '}',
															'emptyDescription' => ''
														]
				
								]
		]);
	
	}
	
	function _End_Loop() {
	
		return $this->defaultDescription();
	
	}
	
	function _Exit_Loop_If() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									'/Calculation'
								],
						
			'format' 		=> 	'$0'
		]);
	
	}
	
	function _Exit_Script() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									'/Calculation'
								],
						
			'format' 		=> 	'$0'
		]);
	
	}
	
	function _Halt_Script() {
	
		return $this->defaultDescription();
	
	}
	
	function _If() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									'/Calculation'
								],
						
			'format' 		=> 	'$0',
			
			'styles'		=> 	[
									'JavaScriptDescriptionStyle' => 	[
															'nameDescriptionSeparator' => ' ',
															'suffix' => ' {'
														]
				
								]
		]);

	
	}
	
	function _Install_OnTimer_Script() {
	
		return $this->defaultDescription();
	
	}
	
	function _Loop() {
	
		return $this->defaultDescription();
	
	}
	
	function _PauseResume_Script() {
	
		return $this->defaultDescription();
	
	}
	
	function _Perform_Script() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/Script['name']",
									"/Script['id']",
									"/Calculation"
								],
						
			'format' 		=> 	'$0 #$1 < $2',
			
			'formatRegex'	=>  [
									'/ < $/i' => ''
				
								],
								
			'styles'		=> 	[
									'JavaScriptDescriptionStyle' => 	[
															'format' => '{scriptId: $1, script: "$0", parameter: $2}'
															
														]
				
								]
		]);
		
	}
	
	function _Perform_Script_On_Server() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/Script['name']",
									"/Script['id']",
									"/Calculation",
									"/WaitForCompletion['state']"
								],
						
			'format' 		=> 	'Wait for completion: $3 ; $0 < $2',
			
			'formatRegex'	=>  [
									'/ < $/i' => ''
				
								]
		]);
	
	}
	
	function _Set_Error_Capture() {
		
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/Set['state']"
								],
						
			'format' 		=> 	'$0',
			
			'postSub'		=>	[
									'False' => 'Off',
									'True' => 'On'
								]
		]);
	
	}
	
	function _Set_Layout_Object_Animation() {
	
		return $this->defaultDescription();
	
	}
	
	function _Set_Variable() {
	
		return $this->defaultDescription([
			
			'styles'		=> 	[
									'JavaScriptDescriptionStyle' =>	[
														'format' => '$0[--Repetition$2] = $1',
														
														'formatRegex'	=>  [
															'/^\$/i' => 'var '
														]
													]
								],
			
			
			'parts' 		=>	[
									'/Name', 
									'/Value/Calculation',
									'/Repetition/Calculation'
								],
						
			'format' 		=> 	'$0[--Repetition$2] ; Value: $1',
			
			'formatSubs' 	=> 	[
									'[--Repetition1]' => '',
									'[--Repetition' => '['
								]
		]);
		
	}
	
	// !Navigation script steps
	
	function _Close_Popover() {
	
		return $this->defaultDescription();
	
	}
	
	function _Enter_Browse_Mode() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/Pause['state']"
									
								],
						
			'format' 		=> 	'Pause: $0',
			
			'postSub'		=> 	[
									'Pause: False' => '',
									'Pause: True' => 'Pause'
								]
			
		]);
	
	}
	
	function _Enter_Find_Mode() {
	
		return $this->defaultDescription();
	
	}
	
	function _Enter_Preview_Mode() {
	
		return $this->defaultDescription();
	
	}
	
	function _Go_to_Field() {
	
		return $this->defaultDescription();
	
	}
	
	function _Go_to_Layout() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/LayoutDestination['value']",
									"/Layout['name']",
									"/Layout['id']"
									
								],
						
			'format' 		=> 	'$0: $1',
			
			'postSub'		=> 	[
									'SelectedLayout: '  => '',
									'OriginalLayout: $1' => 'Original Layout'
								]
		]);

	}
	
	function _Go_to_Next_Field() {
	
		return $this->defaultDescription();
	
	}
	
	function _Go_to_Object() {
	
		return $this->defaultDescription();
	
	}
	
	function _Go_to_Portal_Row() {
	
		return $this->defaultDescription();
	
	}
	
	function _Go_to_Previous_Field() {
	
		return $this->defaultDescription();
	
	}
	
	function _Go_to_RecordRequestPage() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/RowPageLocation['value']"
									
								],
						
			'format' 		=> 	'$0'
		]);
	
	}
	
	function _Go_to_Related_Record() {
	
		return $this->defaultDescription();
	
	}
	
	
	// !Editing script steps
	
	
	function _Clear() {
	
		return $this->defaultDescription();
	
	}
	
	function _Copy() {
	
		return $this->defaultDescription();
	
	}
	
	function _Cut() {
	
		return $this->defaultDescription();
	
	}
	
	function _Paste() {
	
		return $this->defaultDescription();
	
	}
	
	function _Perform_FindReplace() {
	
		return $this->defaultDescription();
	
	}
	
	function _Select_All() {
	
		return $this->defaultDescription();
	
	}
	
	function _Set_Selection() {
	
		return $this->defaultDescription();
	
	}
	
	function _UndoRedo() {
	
		return $this->defaultDescription();
	
	}
	
	
	// !Fields script steps
	
	function _Export_Field_Contents() {
	
		return $this->defaultDescription();
	
	}
	
	function _Insert_AudioVideo() {
	
		return $this->defaultDescription();
	
	}
	
	function _Insert_Calculated_Result() {
	
		return $this->defaultDescription();
	
	}
	
	function _Insert_Current_Date() {
	
		return $this->defaultDescription();
	
	}
	
	function _Insert_Current_Time() {
	
		return $this->defaultDescription();
	
	}
	
	function _Insert_Current_User_Name() {
	
		return $this->defaultDescription();
	
	}
	
	function _Insert_File() {
	
		return $this->defaultDescription();
	
	}
	
	function _Insert_From_Device() {
	
		return $this->defaultDescription();
	
	}
	
	function _Insert_From_Index() {
	
		return $this->defaultDescription();
	
	}
	
	function _Insert_From_Last_Visited() {
	
		return $this->defaultDescription();
	
	}
	
	function _Insert_From_URL() {
	
		return $this->defaultDescription();
	
	}
	
	function _Insert_PDF() {
	
		return $this->defaultDescription();
	
	}
	
	function _Insert_Picture() {
	
		return $this->defaultDescription();
	
	}
	
	function _Insert_Text() {
	
		return $this->defaultDescription();
	
	}
	
	function _Relookup_Field_Contents() {
	
		return $this->defaultDescription();
	
	}
	
	function _Replace_Field_Contents() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/Field['table']",
									"/Field['name']",
									"/With['value']",
									"/Calculation",
									"/NoInteract['state']",
									"/SerialNumbers['UpdateEntryOptions']",
									"/SerialNumbers['UseEntryOptions']",
									"/Field['id']"
									
								],
						
			'format' 		=> 	'$0::$1 = $2 $3',
			
			'postSub'		=>	[
									' = Calculation ' => ' = '
								]
			
		]);

	
	}
	
	function _Set_Field() {
	
		return $this->defaultDescription([
			
			
			
			'parts' 		=>	[
									"/Field['table']",
									"/Field['name']",
									"/Calculation",
									"/Field['id']"
									
								],
						
			'format' 		=> 	'$0::$1 = $2'
			
		]);
	
	}
	
	function _Set_Field_By_Name() {
	
		return $this->defaultDescription();
	
	}
	
	function _Set_Next_Serial_Value() {
	
		return $this->defaultDescription();
	
	}
	
	
	// !Records script steps
	
	function _Commit_RecordsRequests() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/NoInteract['state']",
									"/Option['state']",
									"/ESSForceCommit['state']",
									
								],
						
			'format' 		=> 	'Skip data entry validation: $0, With dialog: $1, Override ESS locking conflicts: $2',
			
			'postSub'		=>	[
									'Skip data entry validation: True' => 'Skip data entry validation',
									'Skip data entry validation: False' => '',
									'With dialog: True' => 'With dialog',
									', With dialog: False' => '',
									'Override ESS locking conflicts: True' => 'Override ESS locking conflicts',
									', Override ESS locking conflicts: False' => '',
									'[ ,' => '[ ',
									'[  ]' => ''
								]
			
		]);
	}
	
	function _Copy_All_RecordsRequests() {
	
		return $this->defaultDescription();
	
	}
	
	function _Copy_RecordRequest() {
	
		return $this->defaultDescription();
	
	}
	
	function _Delete_All_Records() {
	
		return $this->defaultDescription();
	
	}
	
	function _Delete_Portal_Row() {
	
		return $this->defaultDescription();
	
	}
	
	function _Delete_RecordRequest() {
	
		return $this->defaultDescription();
	
	}
	
	function _Duplicate_RecordRequest() {
	
		return $this->defaultDescription();
	
	}
	
	function _Export_Records() {

		return $this->defaultDescription([
			
			'types'					=> 	[
											"/NoInteract"									=> 'boolean/flip',
											"/AutoOpen"										=> 'boolean',
											"/CreateEmail"									=> 'boolean',
											"/Restore"										=> 'boolean',
											"/ExportOptions['FormatUsingCurrentLayout']" 	=> 'boolean'
											
										],
								
			'key_transforms'		=>	[
											"NoInteract"				=>	'With dialog',
											"AutoOpen"					=>	'Open automatically',
											"CreateEmail"				=>	'Create email',
											"Restore"					=>	'Restore',
											"FormatUsingCurrentLayout"	=>	'Format using current layout'
										]
		]);
		
	}
	
	function _Import_Records() {
	
		return $this->defaultDescription();
	
	}
	
	function _New_RecordRequest() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_RecordRequest() {
	
		return $this->defaultDescription();
	
	}
	
	function _Revert_RecordRequest() {
	
		return $this->defaultDescription();
	
	}
	
	function _Save_Records_As_Excel() {
	
		return $this->defaultDescription();
	
	}
	
	function _Save_Records_As_PDF() {
	
		return $this->defaultDescription();
	
	}
	
	function _Save_Records_As_Snapshot_Link() {
	
		return $this->defaultDescription();
	
	}
	
	function _Truncate_Table() {
	
		return $this->defaultDescription();
	
	}
	
	
	// !Found Sets script steps
	
	function _Constrain_Found_Set() {
	
		return $this->defaultDescription();
	
	}
	
	function _Extend_Found_Set() {
	
		return $this->defaultDescription();
	
	}
	
	function _Find_Matching_Records() {
	
		return $this->defaultDescription();
	
	}
	
	function _Modify_Last_Find() {
	
		return $this->defaultDescription();
	
	}
	
	function _Omit_Multiple_Records() {
	
		return $this->defaultDescription();
	
	}
	
	function _Omit_Record() {
	
		return $this->defaultDescription();
	
	}
	
	function _Perform_Find() {
		
		return $this->defaultDescription();

	
	}
	
	function _Perform_Quick_Find() {
	
		return $this->defaultDescription();
	
	}
	
	function _Show_All_Records() {
	
		return $this->defaultDescription();
	
	}
	
	function _Show_Omitted_Only() {
	
		return $this->defaultDescription();
	
	}
	
	function _Sort_Records() {
	
		return $this->defaultDescription();
	
	}
	
	function _Sort_Records_by_Field() {
	
		return $this->defaultDescription();
	
	}
	
	function _Unsort_Records() {
	
		return $this->defaultDescription();
	
	}
	
	
	// !Windows_script_steps
	
	
	function _Adjust_Window() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/WindowState['value']"
									
								],
						
			'format' 		=> 	'$0'
			
		]);
	
	}
	
	function _Arrange_All_Windows() {
	
		return $this->defaultDescription();
	
	}
	
	function _Close_Window() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									'/Window[\'value\']'
								],
						
			'format' 		=> 	'$0',
			
			'postSub'		=> 	[
									'LimitToWindowsOfCurrentFile [ state="True" ]'  => ''
								]
		]);

	
	}
	
	function _Freeze_Window() {
	
		return $this->defaultDescription();
	
	}
	
	function _MoveResize_Window() {
	
		return $this->defaultDescription();
	
	}
	
	function _New_Window() {
	
		return $this->defaultDescription();
	
	}
	
	function _Refresh_Window() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/Option['state']",
									"/FlushSQLData['state']"
									
								],
														
			'format' 		=> 	'Flush cached join results: $0, Flush cached external data: $1',
			
			'postSub'		=>	[
									'Flush cached join results: True' => 'Flush cached join results',
									'Flush cached external data: True' => 'Flush cached external data',
									'Flush cached join results: False' => '',
									'Flush cached external data: False' => '',
									' ,  ' => ''

								]
			
		]);
	
	}
	
	function _Scroll_Window() {
	
		return $this->defaultDescription();
	
	}
	
	function _Select_Window() {
	
		return $this->defaultDescription();
	
	}
	
	function _Set_Window_Title() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/Window['value']",
									"/NewName/Calculation",
									"/LimitToWindowsOfCurrentFile['state']"
									
								],
						
			'format' 		=> 	'Window: $0, $1, LimitToWindowsOfCurrentFile: $2',
			
			'postSub'		=> 	[
									' Window: Current,' => '',
									', LimitToWindowsOfCurrentFile: True' => '',
									', LimitToWindowsOfCurrentFile: False' => ', All files',
								]
			
		]);

	    
	}
	
	function _Set_Zoom_Level() {
	
		return $this->defaultDescription();
	
	}
	
	function _ShowHide_Menubar() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/ShowHide['value']",
									"/Lock['state']"
									
								],
						
			'format' 		=> 	'$0, Lock: $1',
			
			'postSub'		=> 	[
									', Lock: True'  => ', Lock',
									', Lock: False'  => ''
								]
		]);
	
	}
	
	function _ShowHide_Text_Ruler() {
	
		return $this->defaultDescription();
	
	}
	
	function _ShowHide_Toolbars() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/ShowHide['value']",
									"/Lock['state']",
									"/IncludeEditRecordToolbar['state']"
									
								],
						
			'format' 		=> 	'$0, Lock: $1, IncludeEditRecordToolbar: $2',
			
			'postSub'		=> 	[
									', Lock: True'  => ', Lock',
									', Lock: False'  => '',
									', IncludeEditRecordToolbar: False'  => ''
								]
		]);
		
	}
	
	function _View_As() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/View['value']"
									
								],
						
			'format' 		=> 	'$0'
			
		]);

	 	 
	}
	
	
	// !Files script steps
	
	function _Close_File() {
	
		return $this->defaultDescription();
	
	}
	
	function _Convert_File() {
	
		return $this->defaultDescription();
	
	}
	
	function _New_File() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_File() {
	
		return $this->defaultDescription();
	
	}
	
	function _Print() {
	
		return $this->defaultDescription();
	
	}
	
	function _Print_Setup() {
	
		return $this->defaultDescription();
	
	}
	
	function _Recover_File() {
	
		return $this->defaultDescription();
	
	}
	
	function _Save_a_Copy_as() {
	
		return $this->defaultDescription();
	
	}
	
	function _Set_MultiUser() {
	
		return $this->defaultDescription();
	
	}
	
	function _Set_Use_System_Formats() {
	
		return $this->defaultDescription();
	
	}
	
	
	// !Accounts_script_steps
	
	
	function _Add_Account() {
	
		return $this->defaultDescription();
	
	}
	
	function _Change_Password() {
	
		return $this->defaultDescription();
	
	}
	
	function _Delete_Account() {
	
		return $this->defaultDescription();
	
	}
	
	function _Enable_Account() {
	
		return $this->defaultDescription();
	
	}
	
	function _ReLogin() {
	
		return $this->defaultDescription();
	
	}
	
	function _Reset_Account_Password() {
	
		return $this->defaultDescription();
	
	}
	
	
	// !Spelling script steps
	
	function _Check_Found_Set() {
	
		return $this->defaultDescription();
	
	}
	
	function _Check_Record() {
	
		return $this->defaultDescription();
	
	}
	
	function _Check_Selection() {
	
		return $this->defaultDescription();
	
	}
	
	function _Correct_Word() {
	
		return $this->defaultDescription();
	
	}
	
	function _Edit_User_Dictionary() {
	
		return $this->defaultDescription();
	
	}
	
	function _Select_Dictionaries() {
	
		return $this->defaultDescription();
	
	}
	
	function _Spelling_Options() {
	
		return $this->defaultDescription();
	
	}
	
	
	// !Open Menu Item script steps
	
	function _Open_Edit_Saved_Finds() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_File_Options() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_FindReplace() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_Help() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_Launch_Center() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_Manage_Containers() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_Manage_Data_Sources() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_Manage_Database() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_Manage_Layouts() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_Manage_Themes() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_Manage_Value_Lists() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_Preferences() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_Remote() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_Script_Workspace() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_Sharing() {
	
		return $this->defaultDescription();
	
	}
	
	function _Upload_To_FileMaker_Server() {
	
		return $this->defaultDescription();
	
	}
	
	
	// !Miscellaneous script steps
	
	function _Comment() {
	
		return $this->defaultDescription([
			
			'name'			=> 	'',
			
			'parts' 		=>	[
									'/Text'
								],
						
			'format' 		=> 	'# $0',
			
			'postSub'		=>	[
									'# (comment) ' => ''
				
								],
								
			'postReplace'		=>	[
									'# ' => ''
				
								]
		]);
	
	}
	
	function _Allow_Formatting_Bar() {
	
		return $this->defaultDescription();
	
	}
	
	function _AVPlayer_Play() {
	
		return $this->defaultDescription();
	
	}
	
	function _AVPlayer_Set_Options() {
	
		return $this->defaultDescription();
	
	}
	
	function _AVPlayer_Set_Playback_State() {
	
		return $this->defaultDescription();
	
	}
	
	function _Beep() {
	
		return $this->defaultDescription();
	
	}
	
	function _Dial_Phone() {
	
		return $this->defaultDescription();
	
	}
	
	function _Enable_Touch_Keyboard() {
	
		return $this->defaultDescription();
	
	}
	
	function _Execute_SQL() {
	
		return $this->defaultDescription();
	
	}
	
	function _Exit_Application() {
	
		return $this->defaultDescription();
	
	}
	
	function _Flush_Cache_to_Disk() {
	
		return $this->defaultDescription();
	
	}
	
	function _Get_Directory() {
	
		return $this->defaultDescription();
	
	}
	
	function _Install_Menu_Set() {
	
		return $this->defaultDescription();
	
	}
	
	function _Install_PlugIn_File() {
	
		return $this->defaultDescription();
	
	}
	
	function _Open_URL() {
	
		return $this->defaultDescription();
	
	}
	
	function _Perform_AppleScript() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									"/ContentType['value']",
									"/Text",
									"/Calculation"
								],
						
			'format' 		=> 	'$1'			
		]);
	
	}
	
	function _Refresh_Object() {
	
		return $this->defaultDescription();
	
	}
	
	function _Refresh_Portal() {
	
		return $this->defaultDescription();
	
	}
	
	function _Send_DDE_Execute() {
	
		return $this->defaultDescription();
	
	}
	
	function _Send_Event() {
	
		return $this->defaultDescription();
	
	}
	
	function _Send_Mail() {
	
		return $this->defaultDescription();
	
	}
	
	function _Set_Web_Viewer() {
	
		return $this->defaultDescription();
	
	}
	
	function _Show_Custom_Dialog() {
	
		return $this->defaultDescription([
			
			'parts' 		=>	[
									'/Title/Calculation',
									'/Message/Calculation'
								],
						
			'format' 		=> 	'$0 Message: $1',
			
			'postSub'		=>	[
									'Button [ CommitState="False" ] ' => ''
				
								]
		]);

	}
	
	function _Speak() {
		
		return $this->defaultDescription();
		
	}
	
}