<?php

ClassLoader::import("framework.request.validator.RequestValidator");

/**
 * Request Validator and request data container wrapper
 *
 * @package framework.request.validator
 * @author Integry Systems
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
			$this->set($name, $value);
		}
	}

	/**
	 * Sets/overwrites a form field value
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function set($name, $value)
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
	public function get($fieldName)
	{
		if (isset($this->data[$fieldName]))
		{
			return $this->data[$fieldName];
		}
		else
		{
			return null;
		}
	}

	public function getData()
	{
		return $this->data;
	}

	public function getName()
	{
		if ($this->validator != null)
		{
			return $this->validator->getName();
		}
		else
		{
			return null;
		}
	}

	/**
	 * Gets a form validator
	 *
	 * @return RequestValidator
	 */
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
	
	public function clearData()
	{
		$this->data = array();
	}
}

?>