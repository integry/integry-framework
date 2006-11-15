<?php

class Router
{
	private static $instance = null;
	private $routeList = array();
	
	
	public static function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new Router();
		}
		return self::$instance;
	}
	
	public function connect($routeDefinitionPattern, $paramValueAssigments = array(), $paramValueRequirements = array())
	{
		$this->routeList[] = new Route($routeDefinitionPattern, $paramValueAssigments, $paramValueRequirements);
	}
	
	public function mapToRoute($URLStr, Request $request)
	{
		
	}
	
	public function createURL($URLParamList)
	{
		$matchingRoute = null;
		foreach ($this->routeList as $route)
		{
			$routeExpectedParamList = $route->getParamList();
			$routeParamsMatch = true;
			
			$routeParamNames = array_keys($routeExpectedParamList);
			$urlParamNames = array_keys($URLParamList);
			
			$urlParamDiff = array_diff($routeParamNames, $urlParamNames);
			$routeParamDiff = array_diff($urlParamNames, $routeParamNames);
			if (!empty($urlParamDiff) || !empty($routeParamDiff))
			{
				break;
			}
			foreach ($routeExpectedParamList as $paramName => $paramRequirement)
			{
				if (empty($URLParamList[$paramName]))
				{
					$routeParamsMatch = false;
					break;
				}
				else
				{
					if (!preg_match('/^' . $paramRequirement . '/' , $URLParamList[$paramName]))
					{
						$routeParamsMatch = false;
						break;
					}
				}
			}
			if ($routeParamsMatch)
			{
				$matchingRoute = $route;
				break;
			}
		}
		if ($matchingRoute == null)
		{
			throw new RouterException("Unable to map to any route");
		}
		$url = $route->getDefinitionPattern();
		foreach ($URLParamList as $paramName => $value)
		{
			$url = str_replace(":" . $paramName, $value, $url);
		}
		return $url;
	}
}


class Route
{
	private $definitionPattern = "";
	private $recognitionPattern = "";
	private $defaulParamValueRequirement = "[.a-zA-Z0-9]+";
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
	
	public function getParamList()
	{
		return $this->paramList;
	}
	
	public function getRecognitionPattern()
	{
		return $this->recognitionPattern;
	}
	
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
	
	private function isParam($URLPatternPart)
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

$router = Router::getInstance();

$router->connect(":controller", array("action" => "index"));
$router->connect("blog/:action/:id", array("controller" => "blogPost"), array("id" => "[0-9]+"));
$router->connect(":controller/:action/:id", array(), array("id" => "[0-9]+"));

//echo $router->createURL(array("controller" => "testController", "action" => "index"));
echo $router->createURL(array("controller" => "blogPost", "action" => "index", "id" => "1"));

echo "\n<pre>"; print_r($router); echo "</pre>\n";


?>