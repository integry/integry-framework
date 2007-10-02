<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * Checks if a file has been uploaded 
 *
 * @package framework.request.validator.check
 * @author Integry Systems
 */
class IsFileUploadedCheck extends Check
{
	public function isValid($value)
	{
		if (empty($value['tmp_name']))
		{
		  	return false;
		} 
		else
		{
		  	return true;
		}
	}
	
	public function setFieldName($fieldName)
	{
	  	$this->setParam('fieldName', $fieldName);
	}
}

?>