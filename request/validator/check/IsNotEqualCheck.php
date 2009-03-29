<?php

/**
 *
 *
 * @package framework.request.validator.check
 * @author Integry Systems
 */
class IsNotEqualCheck extends Check
{
	public function __construct($violationMsg, $value)
	{
		parent::__construct($violationMsg);
		$this->setParam("value", $value);
	}

	public function isValid($value)
	{
		return $value != $this->getParam("value");
	}
}

?>