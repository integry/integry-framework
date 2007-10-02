<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * ...
 *
 * @package framework.request.validator.check
 * @author Integry Systems
 */
class IsValidEmailCheck extends Check
{
	private $pattern = "";
	
	public function __construct($violationMsg, $pattern) 
	{
		parent::__construct($violationMsg);
		$this->pattern = $pattern;
	}
	
	public function isValid($value)
	{
		return preg_match($this->pattern, $value);
	}
}

?>