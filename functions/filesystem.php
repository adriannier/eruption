<?php

function noSpacesFileName($str) {
   
	return str_replace(' ', '_', trim(filterFileName($str)));
	
}

function filterFileName($str) {
   
	$str = str_replace("\n", ' ', $str);
	$str = str_replace('/', '_', $str);
	$str = str_replace(DIRECTORY_SEPARATOR, '_', $str);
	$str = str_replace(':', '_', $str);
	$str = substr($str, 0, 245);
	
	return $str;
	
}

function checkDirectory($path) {
	
	if (!is_dir($path)) {
		
		if (!mkdir($path, 0777, true)) {
			doLog('Could not create directory at: '.$path);	
			exit();
		}
		
	}

}

function rmdirRecursive($dir) { 
  
	if (is_dir($dir)) { 
		
		$objects = scandir($dir); 
		
		foreach ($objects as $object) { 

			if ($object != "." && $object != "..") { 
				if (is_dir($dir."/".$object)) {
					rmdirRecursive($dir."/".$object);
				} else {
					unlink($dir."/".$object); 
				}
			} 
			
		}
	
		rmdir($dir); 
	} 
	
}

function relativePath($path1, $path2) {
	
	// Break paths into components
	$path1Components = explode('/', $path1);
	$path2Components = explode('/', $path2);
	$path1ComponentCount = count($path1Components);
	$path2ComponentCount = count($path1Components);
	
	// Compare components of both paths and determine at which point they differ
	for ($i = 0 ; $i < $path1ComponentCount ; $i++ ) {
		
		try {
			if ($path1Components[$i] != $path2Components[$i]) {
				break;
			}
		} catch (Exception $e) {
			break;
		}	
		
	}
	
	// If necessary, create a path prefix to go up the hierarchy
	$pathPrefix = '';
	for ($j = 0 ; $j < ($path1ComponentCount - ($i + 1)) ; $j++) {
		$pathPrefix .= '../';
		
	}
	
	// Make sure we’re not out of bounds
	if ($i > ($path2ComponentCount - 1)) {
		$i = ($path2ComponentCount - 1);
	} 	
	
	// Combine components
	$relativePath = $pathPrefix.implode('/', array_slice($path2Components, $i));
	
	return $relativePath;

}