<?php

ClassLoader::import("framework.request.validator.check.CheckException");

/**
 * Variable validation logic container
 *
 */
abstract class Check
{
	protected $violationMsg = "";
	
	public function __construct($violationMsg)
	{
		$this->violationMsg = $violationMsg;
	}
	
	public function getViolationMsg()
	{
		return $this->violationMsg;
	}
	
	abstract public function isValid($value);
}

?>