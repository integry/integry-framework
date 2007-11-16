<?php

ClassLoader::import("framework.response.Response");

/**
 * Input field auto-complete data response
 *
 * @package	framework.response
 * @author Integry Systems
 */
class AutoCompleteResponse extends Response
{
	private $content = "";

	public function __construct($data)
	{
		if (!is_array($data))
		{
		  	throw new Exception('AutoCompleteResponse::__construct needs an array passed!');
		}
				
		$this->content = $data;
	}

	public function getData()
	{
		$listHtml = array('<ul>');
	   	
	  	$li = new HtmlElement('li');
		foreach ($this->content as $key => $value)
		{
			$li->setAttribute('id', $key);
			$li->setContent($value);
			$listHtml[] = $li->render();
		} 
	   
		$listHtml[] = '</ul>';

		return implode("\n", $listHtml);
	}
}

?>
