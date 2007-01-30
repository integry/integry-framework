<?php

ClassLoader::import("framework.response.ActionResponse");

/**
 * Response rendered as a primitive XML document
 */
class XMLResponse extends ActionResponse
{
	public function __construct($header=true)
	{
	    if($header) $this->setHeader("Content-Type", "text/xml");
	}
}
?>
