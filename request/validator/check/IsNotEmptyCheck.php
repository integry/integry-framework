<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * 
 *
 * @package framework.request.validator.check
 * @author Integry Systems
 */
class IsNotEmptyCheck extends Check
{
	public function isValid($value)
	{
		$value = trim($value);
		
		return (strlen($value) > 0);
	}
}

?>