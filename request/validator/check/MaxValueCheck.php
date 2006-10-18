<?php

/**
 * 
 * @package framework.request.validator.check
 */
class MaxValueCheck extends Check
{
	public function __construct($violationMsg, $maxValue)
	{
		parent::__construct($violationMsg);
		$this->setParam("maxValue", $maxValue);
	}
	
	public function isValid($value)
	{
		if ((int)$value <= $this->getParam("maxValue"))
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