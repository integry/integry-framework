<?php

ClassLoader::import("framework.request.validator.check.Check");

class IsNotEmptyCheck extends Check
{
	public function isValid($value)
	{
		$value = trim($value);
		if (!empty($value))
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