<?php

/**
 * Request Validator and request data container wrapper
 *
 * @package framework.request.validator
 *
 */
class Form
{
	/**
	 * Validator instance
	 *
	 * @var RequestValidator
	 */
	private $validator = null;
	
	/**
	 * Form data array
	 *
	 * @var array
	 */
	private $data = array();

	
	public function __construct(RequestValidator $validator)
	{
		$this->validator = $validator;
		if ($validator->hasSavedState())
		{
			$validator->restore();
			$oldRequest = $this->validator->getRestoredRequest();
			if ($oldRequest != null)
			{
				$this->data = $oldRequest->toArray();
			}
		}
	}

	public function setData($data)
	{
		foreach ($data as $name => $value)
		{
			$this->setValue($name, $value);
		}
	}

	/**
	 * Sets/overwrites a form field value
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setValue($name, $value)
	{
		$oldRequest = $this->validator->getRestoredRequest();
		if ($oldRequest != null && !$oldRequest->isValueSet($name))
		{
			$this->data[$name] = $value;
		}
	}

	/**
	 * Gets a form field value
	 *
	 * @param string $fieldName
	 */
	public function getValue($fieldName)
	{
		return $this->data[$fieldName];
	}
}

?>