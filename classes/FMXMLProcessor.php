<?php
	
class FMXMLProcessor {
	
	public $sourceFile;
	public $sourceType;
	public $sourceEncoding;
	
	public $destinationDirectory;
	
	public $template;

	private $xml;

	function __construct($filePath = false, $destinationDirectory = false ) {
		
		if ($filePath !== false) {
			$this->setSourceFile($filePath);
		}
		
		if ($destinationDirectory !== false) {
			$this->setDestinationDirectory($destinationDirectory);
		}
		
	}
	
	function setSourceFile($filePath) {
		
		$this->sourceFile = $filePath;
				
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $this->sourceFile);
		finfo_close($finfo);
		
		if (!in_array($mime, ['application/xml', 'text/html', 'text/plain'])) {
			throwError('Cannot read file at '.$filePath.' because source files of type '.$mime.' are not supported.');
		}
	
    	$fp = fopen($this->sourceFile, 'r');
		$fileStart = trim(fread($fp, 100));
		fclose($fp);
		
		if (stripos($fileStart, '<fmxmlsnippet type="FMObjectList">') !== false) {
			
			$this->sourceType = 'SnippetSourceType';
			$this->stepDescriber = new CBScriptStepDescriber();
			
		} else {
			
			// TODO: Find out if it's actually a template without reading the whole file
			
			$this->sourceType = 'TemplateSourceType';
						
		}
		
	}
	
	function setDestinationDirectory($folderPath) {
		
		if (!endsWith($folderPath, DIRECTORY_SEPARATOR)) {
			$folderPath .= DIRECTORY_SEPARATOR;
		}
		
		$folderPath .= 'Eruption'.DIRECTORY_SEPARATOR;
		
		if ($this->sourceType == 'SnippetSourceType') {
			$folderPath .= 'Clipboard'.DIRECTORY_SEPARATOR;
		}
		
		$this->destinationDirectory = $folderPath;
		
	}
	
	function initializeDestinationDirectory() {
		
		if (is_dir($this->destinationDirectory)) {
			rmdirRecursive($this->destinationDirectory);
		}
		
		mkdir($this->destinationDirectory, 0777, true);
		
		if (!is_dir($this->destinationDirectory)) {
			doLog('Could not create destination directory at: '.$this->destinationDirectory);	
			exit(1);
		}
		
		checkDirectory($this->destinationDirectory);
		
		debugLog('Initialized destination directory at '.$this->destinationDirectory);
		
	}
	
	function process() {

		$startTime = microtime(true);
		
		if ($this->sourceType == 'SnippetSourceType') {
			
			$this->processSnippet();
		
		} else if ($this->sourceType == 'TemplateSourceType') {
			
			$this->processTemplate();
			
		} else {
			
			throwError('Cannot process '.$this->sourceType);
			
		}
		
		$endTime = microtime(true);
		$duration = round($endTime - $startTime, 3); 
		
		doLog('Processed in '.$duration.'s');

	}
	
	function processSnippet() {
		
		$this->initializeDestinationDirectory();
		
		$xml = simplexml_load_file($this->sourceFile);
		
		$this->processClipboardElement(new CBElement($this, $xml, 0));
		
	}
	
	function processTemplate() {
		
		$startTime = microtime(true);
		
		// $this->initializeDestinationDirectory();
		
		$this->template = new DTDocument();
		$this->template->processor = $this;
		$this->template->load($this->sourceFile);

		$endTime = microtime(true);
		$duration = round($endTime - $startTime, 3); 
		
		doLog('Loaded '.$this->sourceFile.' in '.$duration.'s');
							
		$this->template->process();
		
	}
		
	function processClipboardElement($element) {

		$element->writeWithBaseDirectory($this->destinationDirectory);
		
		if ($element->hasProcessableChildren) {
			
			foreach($element->children() as $child) { 
				$this->processClipboardElement($child);

			}
			
		}

	}
	
}