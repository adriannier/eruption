<?php
	
define('DEBUG', false);

// Turn on full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load functions
$funcDir = __DIR__.DIRECTORY_SEPARATOR;
$funcDirItems = scandir($funcDir); 
$ignoredDirItems = ['.', '..', 'load.php'];

foreach ($funcDirItems as $item) { 

	if (!in_array($item, $ignoredDirItems)) { 
		if (substr($item, strlen( $item ) - 4 ) === '.php') {
			include($funcDir.$item);
		}
	}
} 

// Load root and parent classes
$preferedClasses = ['DTElement.php', 'DTLayoutObject.php', 'DTLayoutGroup.php'];
$classesDir = dirname(__DIR__).DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR;

foreach ($preferedClasses as $preferedClass) {
	
	if (is_file($classesDir.$preferedClass)) {
		include($classesDir.$preferedClass);	
	}
	
}

// Load other classes
$classesDirItems = scandir($classesDir); 
$ignoredDirItems = array_merge(['.', '..', ], $preferedClasses);

foreach ($classesDirItems as $item) { 

	if (!in_array($item, $ignoredDirItems)) { 
		if (substr($item, strlen( $item ) - 4 ) === '.php') {
			include($classesDir.$item);
		}
	}
}