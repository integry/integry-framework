<?php

ClassLoader::import("framework.request.validator.check.CheckException");

/**
 * Variable validation logic container
 *
 * @package framework.request.validator.check
 * @author Integry Systems
 */
abstract class Check
{
	protected $violationMsg = "";
	protected $paramList = array();
	
	public function __construct($violationMsg)
	{
		$this->violationMsg = $violationMsg;
	}
	
	public function getViolationMsg()
	{
		return $this->violationMsg;
	}
	
	protected function setParam($name, $value)
	{
		$this->paramList[$name] = $value;
	}
	
	public function getParam($name)
	{
		return $this->paramList[$name];
	}
	
	public function getParamList()
	{
		return $this->paramList;
	}
	
	abstract public function isValid($value);
}

?>