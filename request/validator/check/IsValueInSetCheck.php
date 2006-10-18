<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * Checks if a value exists in a given set
 *
 * @package framework.request.validator.check
 * @author Saulius Rupainis <saulius@integry.net>
 */
class IsValueInSetCheck extends Check
{
	public function __construct($violationMsg, $valueArray)
	{
		parent::__construct($violationMsg);
		$this->setParam("valueArray", $valueArray);
	}
	
	public function isValid()
	{
		return in_array($this->getParam("valueArray"));
	}
}

?>