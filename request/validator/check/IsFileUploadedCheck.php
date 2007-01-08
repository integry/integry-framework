<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * Checks if a file has been uploaded 
 *
 * @package framework.request.validator.check
 * @author Rinalds Uzkalns <rinalds@integry.net>
 */
class IsFileUploadedCheck extends Check
{
	public function isValid($value)
	{
		$field = $this->getParam('fieldName');
		if (!isset($_FILES[$field]) || empty($_FILES[$field]['tmp_name']))
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