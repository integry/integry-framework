<?php

ClassLoader::import('framework.response.RawResponse');

/**
 * Return XML from a SimpleXML object
 *
 * @package	framework.response
 * @author	Integry Systems
 */
class SimpleXMLResponse extends RawResponse
{
	public function __construct(SimpleXMLElement $xml)
	{
		parent::__construct($xml->asXML());
		$this->setHeader('Content-type', 'text/xml');
	}
}

?>