<?php
	
class DTScript extends DTHierarchical {
	
	public $steps = [];
	
	function name() {
		
		$name = trim(parent::name());
		
		if (empty($name)) {
			$name = 'Untitled Script';
		}

		return $name;
				
	}
	
	function describe() {
		
		$indentation = 0;
		$descriptions = [];
		
		foreach ($this->steps as $step) {
			
			if (get_class($step) == 'DTScriptStep') {
				
				if ($step->decreasesIndentation()) {
					$indentation--;
				}
				
				$stepDescription = $step->describe();
				
				for ( $i = 0 ; $i < $indentation ; $i++ ) {
					$stepDescription = "    ".$stepDescription;					
				}
				
				array_push($descriptions, $stepDescription);
				
				if ($step->increasesIndentation()) {
					$indentation++;
				}
							
			} else {
				throwError('Cannot process step of type '.get_class($step));
			}
			
		}
		
		$description = implode("\n", $descriptions);
		$description .= "\n\n".'# Script: '.$this->name().' (id: '.$this->id().')';
		return $description;
		
		
	}
	
	function addSteps($stepNodes) {
		
		$this->ownerDocument->registerNodeClass('DOMElement', 'DTScriptStep');
		$this->steps = [];
		
		foreach ($stepNodes as $stepNode) {
			
			if (get_class($stepNode) == 'DTScriptStep') {
				$this->steps[] = $stepNode;	
			}
			
		}
				
	}
			
}