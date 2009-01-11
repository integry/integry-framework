<?php

/**
 * Application route
 *
 * @author Integry Systems
 * @package framework.request
 */
class Route
{
	/**
	 * Route definition pattern. Route is defined in following way:
	 * :paramName/:secodParam:/someConst
	 *
	 * @var string
	 */
	private $definitionPattern = "";

	/**
	 * Route recognition patern (regexp)
	 *
	 * @var string
	 */
	private $recognitionPattern = "";

	/**
	 * Default requirement for a route param value (regexp)
	 *
	 * @var string
	 */
	private $defaulParamValueRequirement = "-{0,1}[\.a-zA-Z0-9]+";

	/**
	 * The list of static values, that will be registered in request
	 *
	 * @var array
	 */
	private $requestValueAssigments = array();

	/**
	 * Associative parameter array that is encoded into URL and thier coresponding value requirements
	 *
	 * paramName => pattern
	 * secondParam => secondPattern
	 *
	 * @var array
	 */
	private $paramList = array();

	/**
	 * Array holding variable sequence in URL
	 *
	 * For example, for route ":controller/:action" it would be:
	 *
	 * 0 => controller
	 * 1 => action
	 *
	 * @var array
	 */
	private $varList = array();

	/**
	 * Creates a route instance
	 *
	 * @param string $definitionPattern
	 * @param array $paramValueAssigments
	 * @param array $paramValueRequirements
	 */
	public function __construct($definitionPattern, $paramValueAssigments = array(), $paramValueRequirements = array())
	{
		preg_match_all('/\:([a-zA-Z]+)/', $definitionPattern, $matches);

		$this->definitionPattern = $definitionPattern;
		$recognitionPattern = $definitionPattern;

		$rules = array();
		if (count($matches[1]) > 0)
		{
			foreach ($matches[1] as $paramName)
		  	{
				$rules[$paramName] = !empty($paramValueRequirements[$paramName]) ? $paramValueRequirements[$paramName] : $this->defaulParamValueRequirement;
				$recognitionPattern = str_replace(':' . $paramName, '(' . $rules[$paramName] . ')', $recognitionPattern);
			}
		}

		$recognitionPattern = str_replace('/' , '\/', $recognitionPattern);

		$this->recognitionPattern = $recognitionPattern;
		$this->setParamList($rules);
		$this->setVariableList($matches[1]);

		$this->paramList = array_merge($this->paramList, $paramValueAssigments);
		$this->requestValueAssigments = array_merge($this->requestValueAssigments, $paramValueAssigments);

		$this->paramNames = array_keys($this->paramList);
	}

	public function getVariableList()
	{
		return $this->varList;
	}

	public function setVariableList($varList)
	{
		$this->varList = $varList;
	}

	public function setParamList($paramList)
	{
		$this->paramList = $paramList;
	}

	/**
	 * Gets a route param list
	 *
	 * @return array
	 */
	public function getParamList()
	{
		return $this->paramList;
	}

	/**
	 * ...
	 *
	 * @return array
	 */
	public function getRequestValueAssigments()
	{
		return $this->requestValueAssigments;
	}

	/**
	 * Gets a recognition pattern (regexp) for this route
	 *
	 * @return string
	 */
	public function getRecognitionPattern()
	{
		return $this->recognitionPattern;
	}

	/**
	 * Gets a definition pattern for this route
	 *
	 * @return string
	 */
	public function getDefinitionPattern()
	{
		return $this->definitionPattern;
	}

	public function hasMatchingParams(&$paramNames, $nameHash, Router $router)
	{
		if (!isset($this->matchingParams[$nameHash]))
		{
			$this->matchingParams[$nameHash] = !array_diff($paramNames, $this->paramNames);

			if ($this->matchingParams[$nameHash])
			{
				$router->setMatchingRoute($nameHash, $this);
			}
		}

		return $this->matchingParams[$nameHash];
	}
}

?>