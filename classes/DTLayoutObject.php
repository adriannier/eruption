<?php
	
class DTLayoutObject extends DTElement {
	
	public $object;
	public $container;
	
	function initializeChildObject() {
		
		switch ($this->type()) {
		    case 'Button':
		        $obj = $this->firstChild('Button', 'DTButton');
		        break;
		    case 'Edit Box':
		        $obj = $this->firstChild('Field', 'DTEditBox');
		        break;
	        case 'Graphic':
		        $obj = $this->firstChild('Graphic', 'DTGraphic');
		        break;
			case 'Group':
		        $obj = $this->firstChild('GroupedButton', 'DTLayoutGroup');
		        break;
	        case 'Grouped Button':
		        $obj = $this->firstChild('GroupedButton', 'DTLayoutGroup');
		        break;
		}
			
		if (isset($obj) && !is_null($obj)) {
			$this->object = $obj;
			$this->object->parentElement = $this->parentElement;
			$this->object->container = $this;
			$this->object->index = $this->index;
			$this->object->siblingCount = $this->siblingCount;
			$this->object->directory = $this->directory;	
			
		}
		
	}
	
	function alternativeName() {
	
		return $this->name();
			
	}
	
	function fileName() {
		
		$parts = [$this->paddedSiblingNumber()];
		
		if (isset($this->container)) {

			if (!empty($this->container->type())) {
				$parts[] = $this->container->type();
			}
				
		} else {
			
			if (!empty($this->type())) {
				$parts[] = $this->type();	
			}
		}
		
		$objName = $this->name();
				
		if (empty($objName)) {
			$objName = $this->alternativeName();	
		}
		
		if (!empty($objName)) {
			$parts[] = $objName;
		}
			
		$fileName = implode(' ', $parts);
		
		return filterFileName($fileName);
		
	}
	
	function write($overridePath = false) {
		
		$this->initializeChildObject();
		
		if (isset($this->object) && !is_null($this->object)) {
			
			$this->object->writeWithContainer();
			$this->object->writeAdditionalResources();
			
		} else {
			
			$this->writeXML($overridePath);
			
		}
		
	}
	
	function writeWithContainer() {
		
		$this->container->writeXML();
		
	}
	
	function writeBinaryData($directoryName, $path) {
		
		$directory = $this->ownerDocument->pathForResource($directoryName);

		if (!is_null($data = $this->firstChild($path.'BinaryData'))) {
			
			if (!is_null($libRef = $data->firstChild('LibraryReference'))) {
				
				$key = $libRef->attr('key');
			
			}
			
			if (!is_null($streamList = $data->firstChild('StreamList'))) {
				
				$streams = $streamList->childrenAtPath('Stream');
				
				if (count($streams) > 0) {

					if (isset($key) && !empty($key)) {
						$fileName = $key;
					} else if (!is_null($fileNameStream = $this->elementWithName($streams, 'FNAM'))) {
						$fileName = $fileNameStream->textContent;
					}
				
					foreach ($streams as $stream) {
						
						if (!is_null($fileExtension = $this->fileExtensionForStreamName($stream->name()))) {
						
							if ($stream->type() == 'Base64') {
										
								$data = base64_decode($stream->textContent);
								
								if (!isset($fileName)) {
									$thisFileName = md5($data);
								} else {
									$thisFileName = $fileName;
								}
								
								$filePath = $directory.$fileName.$fileExtension;
								
								if (!is_file($filePath)) {
									checkDirectory($directory);
									file_put_contents($filePath, $data);
								}
								
							} else {
								
								$this->warn('Unknown stream type: '.$stream->type());
								
							}
		
						}
											
					}
				
				}
			
			}
			
		}
		
	}
	
	function fileExtensionForStreamName($streamName) {
		
		switch ($streamName) {

			case '8BPS':
				return '.psd';
							
			case 'DPI_':
				return;
														
			case 'FNAM':
				return;
			
			case 'FORK':
				return;

			case 'FRM#':
			return;

			case 'GIFf':
				return '.gif';
									
			case 'GLPH':
				return;
						
			case 'JPEG':
				return '.jpg';

			case 'MAIN':
				return;

			case 'PDF ':
				return '.pdf';

			case 'PNGf':
				return '.png';
				
			case 'SIZE':
				return;

			case 'TIFF':
			return '.tif';
				
			case 'SVG ':
				return '.svg';
	
			default:
				$this->warn('Unknown stream name: '.$streamName, 1);
				return;
		}

	}
	
	
}