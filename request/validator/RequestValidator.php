<?php

ClassLoader::import("framework.request.Request");
ClassLoader::import("framework.request.validator.ValidatorVariable");
ClassLoader::import("framework.request.validator.check.*");
ClassLoader::import("framework.request.validator.filter.*");

/**
 * Request data validator
 *
 * @package framework.request.validator
 * @author Saulius Rupainis <saulius@integry.net>
 *
 */
class RequestValidator
{
	/**
	 * Request instance
	 *
	 * @var Request
	 */
	private $request = null;

	/**
	 * The list of validator variables.
	 *
	 * @var ValidatorVariable[]
	 */
	private $validatorVarList = array();
	
	/**
	 * Validator intance name
	 *
	 * @var string
	 */
	private $name = "";
	
	private $errorList = array();

	/**
	 * Creates a RequestValidator instance
	 *
	 * @param string $name Validator instance name
	 * @param Request $request
	 */
	public function __construct($name, Request $request)
	{
		$this->name = $name;
		$this->request = $request;
	}
	
	public function execute()
	{
		unset($this->errorList);
		foreach ($this->validatorVarList as $var)
		{
			try
			{
				$var->validate();
			} 
			catch(CheckException $e)
			{
				$this->errorList[$var->getName()] = $e->getMessage();
			}
		}
		
		foreach ($this->validatorVarList as $var)
		{
			$var->filter();
		}
	}

	private function getValidatorVar($name)
	{
		if (empty($this->validatorVarList[$name]))
		{
			$this->validatorVarList[$name] = new ValidatorVariable();
		}
		return $this->validatorVarList[$name];
	}

	/**
	 * Applies a check (some kind of requirement for a value) to a request variable
	 *
	 * @param string $varName
	 * @param Check $check
	 */
	public function addCheck($varName, Check $check)
	{
		$this->getValidatorVar($varName)->addCheck($check);
	}

	/**
	 * Assignes a filter to a request variable
	 *
	 * @param string $varName
	 * @param Filter $filter
	 */
	public function addFilter($varName, Filter $filter)
	{
		$this->getValidatorVar($varName)->addFilter($filter);
	}


	public function isValidationFailed()
	{
		@session_start();
		if (!empty($_SESSION['_validator'][$this->name]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function saveState()
	{
		@session_start();
		$_SESSION['_validator'][$this->name] = serialize($this);
	}

	public function restore()
	{
		@session_start();

		$storedValidator = unserialize($_SESSION['_validator'][$this->name]);
		unset($_SESSION['_validator'][$this->name]);
	}
}

?>