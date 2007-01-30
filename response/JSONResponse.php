<?php

ClassLoader::import("framework.response.Response");

/**
 * JSON response
 *
 * @package framework.response
 */
class JSONResponse extends Response
{
    private $content = "";

	public function __construct($data)
	{
        $this->content = json_encode($data);
	}

	public function getData()
	{
	    return $this->content;
	}
}

?>
