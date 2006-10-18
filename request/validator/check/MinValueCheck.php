<?php

class MinValueCheck extends Check
{
	public function __construct($violationMsg, $minValue)
	{
		parent::__construct($violationMsg);
		$this->setParam("minValue", $minValue);
	}
	
	public function isValid($value)
	{
		if ($value >= $this->getParam("minValue"))
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