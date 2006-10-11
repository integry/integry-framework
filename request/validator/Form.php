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
			$this->data = $validator->getData();
		}
	}

	public function setData($data)
	{
		$this->data = $data;
	}

	/**
	 * Sets/overwrites a form field value
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function setValue($name, $value)
	{
		$this->data[$name] = $value;
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