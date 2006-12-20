<?php

/**
 * Maps request variable to checks and filters (validation elements)
 * 
 * Note: this class is protected and should not be used directly. Use a RequestValidator 
 * instead.
 *
 * @see RequestValidator
 * @package framework.request.validator
 * @author Saulius Rupainis <saulius@integry.net>
 */
class ValidatorVariable
{
	/**
	 * List of Check subclass objects
	 *
	 * @var Check[]
	 */
	private $checkList = array();
	
	/**
	 * List of Filter subclass objects
	 *
	 * @var Filter[]
	 */
	private $filterList = array();

	/**
	 * Request variable name that is going to be mapped to checks and filters
	 *
	 * @var string
	 */
	private $varName = "";
	
	/**
	 * Request instance
	 *
	 * @var Request
	 */
	private $request = null;

	public function __construct($varName, Request $request)
	{
		$this->varName = $varName;
		$this->request = $request;
	}

	public function addCheck(Check $check)
	{
		$this->checkList[] = $check;
	}

	public function addFilter(Filter $filter)
	{
		$this->filterList[] = $filter;
	}
	
	public function getName()
	{
		return $this->varName;
	}
	
	/**
	 * Applies validation rules to a request variable
	 *
	 * @throws CheckException when validation fails
	 */
	public function validate()
	{
		foreach ($this->checkList as $check)
		{
			if (!$check->isValid($this->request->getValue($this->varName)))
			{
				throw new CheckException($check->getViolationMsg());
			}
		}
	}
	
	/**
	 * Applies all registered filters sequentialy to a value
	 *
	 */
	public function filter()
	{
		foreach ($this->filterList as $filter)
		{
			$this->request->setValue($this->varName, 
									 $filter->apply($this->request->getValue($this->varName)));
		}
	}
	
	public function getCheckData()
	{
		$data = array();
		foreach ($this->checkList as $check)
		{
			$name = get_class($check);
			$constraintList = $check->getParamList();
			$errMsg = $check->getViolationMsg();
			$data[$name] = array("error" => $errMsg, 
								 "param" => $constraintList);
		}
		return $data;
	}
	
	public function getFilterData()
	{
		$data = array();
		foreach ($this->filterList as $filter)
		{
			$name = get_class($filter);
//			$constraintList = $filter->getParamList();
			$data[$name] = array('');
		}
		return $data;
	}

}

?>