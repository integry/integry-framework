<?php

/**
 * Abstract request value filter
 *
 * @package framework.request.validator.filter
 * @author Integry Systems
 */
abstract class RequestFilter
{
	private $paramList = array();
	
	protected function setParam($name, $value)
	{
		$this->paramList[$name] = $value;
	}

	public function getParam($name)
	{
		return $this->paramList[$name];
	}
	
	public function getParamList()
	{
		return $this->paramList;
	}

	abstract public function apply($value);	
}

?>