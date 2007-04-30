<?php

include_once('Filter.php');

/**
 * Filter characters from strings using custom regular expressions
 *
 * @package framework.request.validator.filter
 * @author Integry Systems
 */
class RegexFilter extends Filter
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