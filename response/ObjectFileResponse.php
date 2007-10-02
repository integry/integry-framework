<?php

ClassLoader::import("framework.response.Response");

/**
 * JSON response
 *
 * @package framework.response
 * @author	Integry Systems 
 */
class ObjectFileResponse extends Response
{
	public function __construct(ObjectFile $objectFile)
	{
	    $this->setHeader('Cache-Control', 'no-cache, must-revalidate');
	    $this->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
	    $this->setHeader('Content-type', $objectFile->getMimeType());
	    $this->setHeader('Content-Disposition', 'attachment; filename="'.$objectFile->getBaseName().'"');
	    $this->setHeader('Content-Length', (string)$objectFile->getSize());
	    
        $this->content = $objectFile->getContents();
	}

	public function getData()
	{
	    return $this->content;
	}
}

?>
