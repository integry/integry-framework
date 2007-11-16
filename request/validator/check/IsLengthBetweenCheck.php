<?php

/**
 * 
 * @package framework.request.validator.check
 * @author Integry Systems
 */
class IsLengthBetweenCheck extends Check
{
	public function __construct($violationMsg, $minLength, $maxLength, $allowEmpty = false)
	{
		parent::__construct($violationMsg);
		$this->setParam('minLength', $minLength);
		$this->setParam('maxLength', $maxLength);
		$this->setParam('allowEmpty', $allowEmpty);
	}
	
	public function isValid($value)
	{
		$len = strlen($value);
		if (($len >= $this->getParam('minLength') && $len <= $this->getParam("maxLength")) || ($len == 0 && $this->getParam("allowEmpty")))
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