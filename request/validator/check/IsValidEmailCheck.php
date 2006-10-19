<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * E-mail address validator class
 *
 * @package framework.request.validator.check
 * @author Saulius Rupainis <saulius@integry.net>
 */
class IsValidEmailCheck extends Check
{
	public function isValid($value)
	{
		return preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/" , $value);
	}
}

?>