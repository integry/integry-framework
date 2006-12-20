<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * Checks if the value is numeric 
 *
 * @package framework.request.validator.check
 * @author Rinalds Uzkalns <rinalds@integry.net>
 */
class IsNumericCheck extends Check
{
	public function isValid($value)
	{
		$value = trim($value);
		return is_numeric($value);
	}
}

?>