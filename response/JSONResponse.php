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
	    $this->setHeader('Cache-Control', 'no-cache, must-revalidate');
	    $this->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
	    
        $this->content = json_encode($data);
	}

	public function getData()
	{
	    return $this->content;
	}
}

?>
