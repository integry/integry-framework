<?php

/**
 * ...
 *
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

	private $varName = "";
	
	/**
	 * Request instance
	 *
	 * @var Request
	 */
	private $request = null;

	public function __construct($varName, Request $request){
		$this->varName = $varName;
		$this->request = $request;
	}

	public function addCheck(Check $check){
		$this->checkList[] = $check;
	}

	public function addFilter(Filter $filter){
		$this->filterList[] = $filter;
	}
	
	public function getName()
	{
		return $this->varName;
	}
	
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
}

?>
