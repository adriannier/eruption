<?php
	
define('DEBUG', false);

// Turn on full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load functions
$funcDir = __DIR__.DIRECTORY_SEPARATOR.'functions'.DIRECTORY_SEPARATOR;
$funcDirItems = scandir($funcDir); 
$ignoredDirItems = ['.', '..'];

foreach ($funcDirItems as $item) { 

	if (!in_array($item, $ignoredDirItems)) { 
		if (substr($item, strlen( $item ) - 4 ) === '.php') {
			include($funcDir.$item);
		}
	}
} 

// Load classes
$preferedClasses = ['DTElement.php', 'DTLayoutObject.php', 'DTLayoutGroup.php'];
$classesDir = __DIR__.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR;
$classesDirItems = scandir($classesDir); 
$ignoredDirItems = array_merge(['.', '..', ], $preferedClasses);

foreach ($preferedClasses as $preferedClass) {
	
	if (is_file($classesDir.$preferedClass)) {
		include($classesDir.$preferedClass);	
	}
	
}

foreach ($classesDirItems as $item) { 

	if (!in_array($item, $ignoredDirItems)) { 
		if (substr($item, strlen( $item ) - 4 ) === '.php') {
			include($classesDir.$item);
		}
	}
}