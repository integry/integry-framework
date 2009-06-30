<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * Checks if a file size does not exceed predefined max size
 *
 * @package framework.request.validator.check
 * @author Integry Systems
 */
class MaxFileSizeCheck extends Check
{
	private $extensions;

	public function __construct($violationMsg, $maxSize)
	{
		parent::__construct($violationMsg);
		$this->setParam('maxSize', $maxSize);
	}

	public function isValid($value)
	{
		$maxSize = $this->getParam('maxSize');
		if (!$maxSize)
		{
			return true;
		}

		return $value['size'] < ($maxSize * 1024 * 1024);
	}
}

?>