<?php

/**
 * 
 * @package framework.request.validator.check
 */
class IsLengthBetweenCheck extends Check
{
	public function __construct($violationMsg, $minLength, $maxLength)
	{
		parent::__construct($violationMsg);
		$this->setParam('minLength', $minLength);
		$this->setParam('maxLength', $maxLength);
	}
	
	public function isValid($value)
	{
		if ((strlen($value) >= $this->getParam('minLength')) && (strlen($value) <= $this->getParam("maxLength")))
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