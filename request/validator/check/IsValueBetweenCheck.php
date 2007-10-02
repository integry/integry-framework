<?php

/**
 * 
 *
 * @package framework.request.validator.check
 * @author Integry Systems
 */
class IsValueBetweenCheck extends Check
{
	public function __construct($violationMsg, $rangeStart, $rangeEnd)
	{
		parent::__construct($violationMsg);
		$this->setParam("rangeStart", $rangeStart);
		$this->setParam("rangeEnd", $rangeEnd);
	}
	
	public function isValid($value)
	{
		if (($value >= $this->getParam("rangeStart")) && ($value <= $this->getParam("rangeEnd")))
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