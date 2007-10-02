<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * Check for max string length validation (fails if string is longer than $maxLength)
 *
 * @package framework.request.validator.check
 * @author Integry Systems
 */
class MaxLengthCheck extends Check
{
	public function __construct($violationMsg, $maxLength)
	{
		parent::__construct($violationMsg);
		$this->setParam("maxLength", $maxLength);
	}
	
	public function isValid($value)
	{
		if (strlen($value) <= $this->getParam("maxLength"))
		{
			return true;
		}
		else 
		{
			return false;
		}
	}
}

?>