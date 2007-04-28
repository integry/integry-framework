<?php

/**
 * Filter characters from strings using custom regular expressions
 *
 * @package framework.request.validator.filter
 * @author Integry Systems
 */
class RegexFilter extends Filter
{
	protected $regex;
	
	public function __construct($regex)
	{
		$this->regex = $regex;	
	}
	
	public function apply($value)
	{
		return preg_replace($this->regex, '', $value);
	}
}

?>