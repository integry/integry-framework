<?php

ClassLoader::import('framework.response.ResponseException');

/**
 * ...
 *
 * @package		framework.response
 */
class HeadersSentException extends ResponseException 
{
	/**
	 * Constructs object
	 */
	public function __construct() 
  {
		$file = null;
		$line = null;
		headers_sent($file, $line);
		
		parent::__construct("Headers have been already sent in ($file) on line $line");
	}
}

?>
