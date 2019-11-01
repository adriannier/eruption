<?php

function caller($stackOffset = 0) {
	
	$trace = debug_backtrace();
	$caller1 = $trace[1 + $stackOffset];
	$caller2 = $trace[2 + $stackOffset];

	$name = $caller2['function'].'()';
	
	if (isset($caller2['class'])) {
	    $name = $caller2['class'].'::'.$name;
	}

	if (isset($caller1['line'])) {
	    $name .= ', Line '.$caller1['line'];
	}
	
    return $name;
    
}

function throwError($errorMessage) {

	throw new Exception($errorMessage);

}
	
function doLog($var = "") {
	
	echo(varDescription($var)."\n");
	
}

function debugLog($var = "") {

	if (defined('DEBUG') && DEBUG === true) {

		doLog($var);
		
	}
			
}

function varDescription($var) {
	
	$varType = gettype($var);
		
	if ($varType == 'object') {
		
		$varClass = get_class($var);
		
		$var = var_export($var, true);
		
	} else if ($varType != 'string') {
		
		$var = json_encode($var);
		
	}
	
	return $var;

}

function startsWith($haystack, $needle) {
	
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
     
}

function endsWith($haystack, $needle) {
	
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
    
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

function formatXML($xml) {
	
	$dom = new DOMDocument;

    $dom->preserveWhiteSpace = false;
    $dom->loadXML($xml);
    $dom->formatOutput = true;
    
    $source = $dom->saveXML();
	$source = str_replace('  ', chr(9), $source);
	
	return $source;
	
}

function d($var, $comment = 'Dumping', $stackOffset = 0) {

	logDivider(caller($stackOffset).': '.$comment.'', 'start');
	
	var_dump($var);
	
	echo("\n");
	logDivider(caller($stackOffset).': End of '.lcfirst($comment), 'end');
	
}

function dd($var, $comment = '', $stackOffset = 0) {
	

	logDivider(caller($stackOffset).': Exiting early'.(empty($comment) ? '' : '; '.$comment));
	
	var_dump($var);
	echo("\n\n");
	exit();
	
}

function de($var, $comment = '', $stackOffset = 0) {
	
	logDivider(caller($stackOffset).': Exiting early'.(empty($comment) ? '' : '; '.$comment));
	
	echo($var);
	echo("\n\n");
	exit();
	
}

function callerLogMark($msg = '', $stackOffset = 0) {
	
	logDivider(caller($stackOffset).(empty($msg) ? '' : ': '.$msg));		
	
}

function logDivider($msg = '', $type = false) {
	
	if (!empty($msg)) {
		
		$msg = ' '.trim($msg).' ';

		if ($type == 'start') {
			$divider1 = "\n".str_pad("", strlen($msg) + 7, "=");
			$divider2 = str_pad("", strlen($msg) + 7, "-");
		} else if ($type == 'end') {
			$divider1 = str_pad("", strlen($msg), "-");
			$divider2 = str_pad("", strlen($msg), "=");			
		} else if ($type == 'thin') {
			$divider1 = str_pad("", strlen($msg), "-");
			$divider2 = $divider1;
		} else {
			$divider1 = str_pad("", strlen($msg), "=");
			$divider2 = $divider1;			
		}

		echo("$divider1\n");
		echo($msg);
		echo("\n$divider2\n");
		echo("\n");
		
	} else {
		
		if ($type == 'thin') {
			echo('--------------------------------------------------------------');
		} else {
			echo('==============================================================');			
		}

		echo("\n\n");
		
	}
	
}

function phpSource($function, $class = null, $contentOnly = false) {

	if (!empty($class)) {
		$func = new ReflectionMethod($class, $function);
	} else {
		$func = new ReflectionFunction($function);
	}

	$fileName = $func->getFileName();
	
	$startLine = $func->getStartLine();
	$endLine = $func->getEndLine();
	
	if ($contentOnly) {
		$startLine++;
		$endLine--;
	}
	
	$fileHandle = fopen($fileName, 'r');
	$lineIndex = 0;
	$foundLines = [];
	while ($line = fgets($fileHandle)) {
		
		$lineIndex++;
		if ($lineIndex >= $startLine) {
			$foundLines[] = $line;
		}
		if ($lineIndex == $endLine) {
			break;
		}
	}
	fclose($fileHandle);
	
	return implode('', $foundLines);
	
}