<?php

/**
 * Request Validator and request data container wrapper
 *
 * @package framework.request.validator
 *
 */
class Form
{
	private $validator = null;
	private $data = array();

	public function __construct(RequestValidator $validator)
	{
		$this->validator = $validator;
		if ($validator->hasErrors())
		{
			$validator->restore();
			$data = $validator->getData();
		}
	}

	public function setData($data)
	{
		$this->data = $data;
	}

	public function setValue($name, $value)
	{
		$this->data[$name] = $value;
	}

	public function getValue($fieldName){

	}
}

?>
