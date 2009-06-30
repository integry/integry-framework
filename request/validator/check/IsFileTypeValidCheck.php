<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * Checks if a file with the correct extension has been uploaded
 *
 * @package framework.request.validator.check
 * @author Integry Systems
 */
class IsFileTypeValidCheck extends Check
{
	private $extensions;

	public function __construct($violationMsg, $extensions)
	{
		parent::__construct($violationMsg);
		$this->setParam('extensions', $extensions);
	}

	public function isValid($value)
	{
		if (!empty($value) || empty($value['name']))
		{
			return true;
		}

		$ext = trim(strtolower(pathinfo($value['name'], PATHINFO_EXTENSION)));
		return in_array($ext, $this->getParam('extensions'));
	}
}

?>