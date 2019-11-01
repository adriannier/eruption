<?php
	
function methodNameForScriptStepName($stepName) {
	
	$methodName = '_'.preg_replace("/[^a-zA-Z0-9_]+/", "", str_replace(' ', '_', $stepName));

	If ($methodName == '__comment') {
		$methodName = '_Comment';
	}
				
	return $methodName;
	
}