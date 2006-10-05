<?php

ClassLoader::import("framework.response.ActionResponse");

/**
 * Response rendered as a primitive XML document
 */
class XMLResponse extends ActionResponse {
	
	private $errorMsg = "";
	private $errorCode = "";
	
	
	public function __construct() {
		$this->setHeader("Content-Type", "text/xml");
	}
	
	public function setError($msg, $code) {
		$this->errorCode = $code;
		$this->errorMsg = $msg;
	}
	
	public function render(Renderer $renderer, $view) {
		
		if (!empty($this->objectContainer) || !empty($this->arrayContainer)) {
			throw new ResponseException("Rendering of complex data types is not implemented");
		}
		
		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= "<response>";
		if (!empty($this->errorMsg)) {
			$xml .= '<error code="' . $this->errorCode . '">' . $this->errorMsg . '</error>';
		} else {
			foreach ($this->dataContainer as $name => $value) {
				$renderer->setValue($name, $value);
				$xml .= '<var name="' . $name . '">' . $value . '</var>';
			}
		}
		$xml .= "</response>";
		
		echo $xml;
	}
}

?>