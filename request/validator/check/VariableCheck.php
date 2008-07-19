<?php

ClassLoader::import("framework.request.validator.check.Check");

/**
 * Apply any Checks to arbitrary runtime variable values (rather than request values)
 *
 * <code>
 * 	$validator->addCheck('formFieldOrSomeIdentifier', new VariableCheck($_COOKIE['myCookie'], new IsNotEmptyCheck("Sorry, cookie not set!")));
  * </code>
 *
 * @package framework.request.validator.check
 * @author Integry Systems
 */
class VariableCheck extends Check
{
	protected $check;
	protected $value;

	public function __construct($value, Check $check)
	{
		parent::__construct($check->getViolationMsg());
		$this->check = $check;
		$this->value = $value;
	}

	public function isValid($value)
	{
		return $this->check->isValid($this->value);
	}
}

?>