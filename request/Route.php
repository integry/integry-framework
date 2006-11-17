<?php

/**
 * Application route
 *
 * @author Saulius Rupainis <saulius@integry.net>
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
	private $defaulParamValueRequirement = "[\.a-zA-Z0-9]+";
	
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
	 * Creates a route instance
	 *
	 * @param string $definitionPattern
	 * @param array $paramValueAssigments
	 * @param array $paramValueRequirements
	 */
	public function __construct($definitionPattern, $paramValueAssigments = array(), $paramValueRequirements = array())
	{
		$this->definitionPattern = $definitionPattern;
		$definitionParts = explode("/", $this->definitionPattern);
		foreach ($definitionParts as $part)
		{
			if ($this->isParam($part))
			{
				$paramName = substr($part, 1);
				if (!empty($paramValueRequirements[$paramName]))
				{
					$this->appendRecognitionPattern($paramValueRequirements[$paramName]);
					$this->registerParam($paramName, $paramValueRequirements[$paramName]);
				}
				else
				{
					$this->appendRecognitionPattern($this->defaulParamValueRequirement);
					$this->registerParam($paramName, $this->defaulParamValueRequirement);
				}
			}
			else
			{
				$this->appendRecognitionPattern($part);
			}
		}
		foreach ($paramValueAssigments as $param => $value)
		{
			$this->registerParam($param, $value);
			$this->registerRequestValueAssigment($param, $value);
		}
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
	
	private function registerParam($name, $valueRequirement)
	{
		$this->paramList[$name] = $valueRequirement;
	}
	
	private function registerRequestValueAssigment($name, $value)
	{
		$this->requestValueAssigments[$name] = $value;
	}
	
	private function appendRecognitionPattern($requirement)
	{
		if (!empty($this->recognitionPattern))
		{
			$this->recognitionPattern .= "\/" . $requirement;
		}
		else
		{
			$this->recognitionPattern = $requirement;
		}
	}
	
	/**
	 * Checks if a given string is a parameter from a route definition pattern (must 
	 * have ":" at the beginnig)
	 *
	 * @param string $URLPatternPart
	 * @return bool
	 */
	public function isParam($URLPatternPart)
	{
		if (substr($URLPatternPart, 0, 1) == ":")
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

?>