<?php

ClassLoader::import("framework.response.Response");

/**
 * JSON response
 *
 * @package framework.response
 * @author	Integry Systems 
 */
class JSONResponse extends Response
{
    private $content = "";

	public function __construct($data, $status = false, $message = false)
	{
	    $this->setHeader('Cache-Control', 'no-cache, must-revalidate');
	    $this->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
	    $this->setHeader('Content-type', 'text/javascript');
        
	    if($message)
	    {
	        $data['message'] = $message;
	    }
	    
	    if($status)
	    {
	        $data['status'] = strtolower($status);
	    }
	    
        $this->content = @json_encode($data);
	}

	public function getData()
	{
	    return $this->content;
	}
}

?>
