<?php

/**
 *  Decorates Check object
 *
 *  @package framework.request.validator.check
 *  @author Integry Systems
 */
class ConditionalCheck extends Check
{
	protected $check;
	protected $condition;
		
	function __construct(CheckCondition $condition, Check $check)
	{
		$this->check = $check;	
		$this->condition = $condition;	
	}	
	
	public function getViolationMsg()
	{
		return $this->check->getViolationMsg();
	}
	
	protected function setParam($name, $value)
	{
		$this->check->setParam($name, $value);
	}
	
	public function getParam($name)
	{
		return $this->check->getParam($name);
	}
	
	public function getParamList()
	{
		return $this->check->getParamList();
	}
	
	public function isValid($value)
	{
		if ($this->condition->isSatisfied())
		{
			return $this->check->isValid($value);		 
		}
		else
		{
			// skip validation if the condition is not satisfied
			return true;
		}
	}	
}

?>