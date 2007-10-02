<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * 
 * @package framework.request.validator.check
 * @author Integry Systems
 */
class MinLengthCheck extends Check
{
	public function __construct($violationMsg, $minLength)
	{
		parent::__construct($violationMsg);
		$this->setParam("minLength", $minLength);
	}
	
	public function isValid($value)
	{
		if (strlen($value) >= $this->getParam("minLength"))
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