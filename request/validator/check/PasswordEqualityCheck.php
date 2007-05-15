<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * 
 * @package framework.request.validator.check
 */
class PasswordEqualityCheck extends Check
{
	public function __construct($violationMsg, $expectedValue, $secondPasswordFieldname)
	{
		parent::__construct($violationMsg);
		$this->setParam("expectedValue", $expectedValue);
		$this->setParam("secondPasswordFieldname", $secondPasswordFieldname);
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