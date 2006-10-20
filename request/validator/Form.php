<?php

ClassLoader::import("framework.request.validator.RequestValidator");

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

	private $enableClientSideValidation = true;
	
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
		if ($oldRequest != null)
		{
			if (!$oldRequest->isValueSet($name))
			{
				$this->data[$name] = $value;
			}
		}
		else
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
		if (!empty($this->data[$fieldName]))
		{
			return $this->data[$fieldName];
		}
		else
		{
			return null;
		}
	}
	
	public function getValidator()
	{
		return $this->validator;
	}
	
	public function enableClientSideValidation($flag = true)
	{
		$this->enableClientSideValidation  = $flag;
	}
	
	public function isClientSideValidationEnabled()
	{
		return $this->enableClientSideValidation;
	}
}

?>