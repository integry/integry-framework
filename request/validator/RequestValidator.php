<?php

ClassLoader::import("framework.request.Request");

/**
 * Request data validator
 *
 * @package framework.request.validator
 * @author Saulius Rupainis <saulius@integry.net>
 * 
 */
class RequestValidator {
	
	/**
	 * Request instance
	 *
	 * @var Request
	 */
	private $request = null;
	
	
	private $validatorVarList = array();
	private $name = "";
	
	/**
	 * Creates a RequestValidator instance
	 *
	 * @param string $name Validator instance name
	 * @param Request $request
	 */
	public function __construct($name, Request $request) {
		$this->name = $name;
		$this->request = $request;
	}
	
	private function getValidatorVar($name) {
		if (empty($this->validatorVarList[$name])) {
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
	public function addCheck($varName, Check $check) {
		$this->getValidatorVar($varName)->addCheck($check);
	}
	
	/**
	 * Assignes a filter to a request variable
	 *
	 * @param string $varName
	 * @param Filter $filter
	 */
	public function addFilter($varName, Filter $filter) {
		$this->getValidatorVar($varName)->addFilter($filter);
	}
	
	
	public function isValidationFailed() {
		@session_start();
		if (!empty($_SESSION['_validator'][$this->name])) {
			return true;
		} else {
			return false;
		}
	}
	
	public function saveState() {
		@session_start();
		$_SESSION['_validator'][$this->name] = serialize($this);
	}
	
	public function restore() {
		@session_start();

		$storedValidator = unserialize($_SESSION['_validator'][$this->name]);
		unset($_SESSION['_validator'][$this->name]);
	}
}

?>