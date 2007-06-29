<?php

include_once('RequestFilter.php');

/**
 * Filter characters from strings using custom regular expressions
 *
 * @package framework.request.validator.filter
 * @author Integry Systems
 */
class RegexFilter extends RequestFilter
{
	public function __construct($regex)
	{
		$this->setParam('regex', $regex);	
	}
	
	public function apply($value)
	{
		return preg_replace('/' . $this->getParam('regex') . '/', '', $value);
	}
}

?>