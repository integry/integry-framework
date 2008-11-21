<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * A composite check - at least one of the checks must pass
 *
 * @package framework.request.validator.check
 * @author Integry Systems
 */
class OrCheck extends Check
{
	protected $fieldNames = array();

	protected $checks = array();

	protected $request;

	/**
	 * @param array $fieldNames	Array of field names used
	 * @param array $checks		Array of respective checks (fieldname and check array indexes must match)
	 */
	public function __construct($fieldNames, $checks, Request $request)
	{
		// get error message from first check
		foreach ($checks as $check)
		{
			if ($violationMsg = $check->getViolationMsg())
			{
				break;
			}
		}

		// check if fieldname and check array indexes match
		if (array_diff(array_keys($fieldNames), array_keys($checks)))
		{
			throw new ApplicationException('Field name and check array keys do not match');
		}

		parent::__construct($violationMsg);

		$this->fieldNames = $fieldNames;
		$this->checks = $checks;
		$this->request = $request;
	}

	public function isValid($value)
	{
		$isValid = false;

		foreach ($this->fieldNames as $key => $fieldName)
		{
			if ($this->checks[$key]->isValid($this->request->get($fieldName)))
			{
				$isValid = true;
				break;
			}
		}

		return $isValid;
	}

	public function getParamList()
	{
		$params = array();
		foreach ($this->fieldNames as $key => $field)
		{
			$params[] = array($field, get_class($this->checks[$key]), $this->checks[$key]->getParamList());
		}

		return $params;
	}
}

?>