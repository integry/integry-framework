<?php

ClassLoader::import("framework.response.Response");

/**
 * Response rendered as a primitive XML document
 */
class JSONResponse extends Response
{
    private $content = "";

	public function __construct($data)
	{
        ClassLoader::import('library.json.Services_JSON');
        $json = new Services_JSON();
        $this->content = $json->encode($data);
	}

	public function getData()
	{
	    return $this->content;
	}
}

?>
