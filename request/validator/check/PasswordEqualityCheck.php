<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * 
 * @package framework.request.validator.check
 */
class PasswordEqualityCheck extends Check
{
	public function __construct($violationMsg, $expectedValue)
	{
		parent::__construct($violationMsg);
		$this->setParam("expectedValue", $expectedValue);
	}
	
	public function isValid($value)
	{
		if (strlen($value) == $this->getParam("expectedValue"))
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