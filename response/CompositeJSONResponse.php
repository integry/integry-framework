<?php

ClassLoader::import("framework.response.CompositeResponse");

/**
 * Composite JSON response - allows to generate multiple page fragments within one request
 *
 * Useful with AJAX calls for which a single request may affect several parts of the user interface
 *
 * @package framework.response
 */
class CompositeJSONResponse extends CompositeResponse
{
	private $data = array();
	
    public function __construct()
	{
	    $this->setHeader('Cache-Control', 'no-cache, must-revalidate');
	    $this->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
	    $this->setHeader('Content-type', 'text/javascript');	    
	}

	public function set($key, $value)
	{
	    $this->data[$key] = $value;
	}

	public function getData()
	{
	    return json_encode($this->data);
	}
}

?>
