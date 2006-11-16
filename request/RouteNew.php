<?php

/**
 * Application router
 * 
 * @author Saulius Rupainis <saulius@integry.net>
 * @package framework.request
 */
class Router
{
	/**
	 * Router instance (sigleton)
	 *
	 * @var Router
	 */
	private static $instance = null;
	
	/**
	 * The list of defined routes
	 *
	 * @var Route[]
	 */
	private $routeList = array();
	
	/**
	 * Default controller name
	 *
	 * @var string
	 */
	public static $defaultController = "index";
	
	/**
	 * Default action name
	 *
	 * @var string
	 */
	public static $defaultAction = "index";
	
	/**
	 * Application base dir
	 * E.x. www.example.com/myapplication/dir/ base dir is /myapplication/dir/
	 *
	 * @var string
	 */
	public static $baseDir = "";
	
	/**
	 * Base url
	 *
	 * @var string
	 */
	public static $baseUrl = "";
	
	/**
	 * Identifies if mod_rewrite is enabled
	 * Should be set manually by using setURLRewrite(flag)
	 *
	 * @var bool
	 */
	private $isURLRewriteEnabled = true;
	
	/**
	 * Router constructor
	 * 
	 * @todo Add https and port to baseUrl
	 */
	private function __construct()
	{
		self::$baseDir = dirname($_SERVER['PHP_SELF']) . '/';
		self::$baseUrl = 'http://' . $_SERVER['SERVER_NAME'] . self::$baseDir;
	}
	
	/**
	 * Gets a base url
	 *
	 * @return string
	 */
	public function getBaseDir()
	{
		return self::$baseDir;
	}
	
	/**
	 * Gets a base directory
	 *
	 * @return string
	 */
	public function getBaseUrl()
	{
		return self::$baseUrl;
	}
	
	/**
	 * Creates a singleton instance
	 *
	 * @return Router
	 */
	public static function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new Router();
		}
		return self::$instance;
	}
	
	
	/**
	 * Connects a new route to some URL pattern.
	 * URLPattern might have "variables", which has a ":" at the beggining. e.x. ":action"
	 *
	 * E.x.:
	 * <code>
	 * $router->connect(":controller/:action/:id", array(), array("id" => "[0-9]+"));
	 * </code>
	 *
	 * The route above will map to following URL's:
	 * item/add/34
	 * post/view/9233
	 *
	 * But not to these:
	 * item/add/AC331
	 * post/view
	 *
	 * URLPattern variables :controller :action :id by default might have value from
	 * a range [_.a-zA-Z0-9]. When you pass array("id" => "[0-9]") as varRequirements
	 * id is required to be only numeric.
	 *
	 *
	 * @link http://www.symfony-project.com/book/trunk/routing
	 *
	 * @param string $routeDefinitionPattern
	 * @param array $paramValueAssigments
	 * @param array $paramValueRequirements
	 *
	 */
	public function connect($routeDefinitionPattern, $paramValueAssigments = array(), $paramValueRequirements = array())
	{
		$this->routeList[] = new Route($routeDefinitionPattern, $paramValueAssigments, $paramValueRequirements);
	}
	
	public function mapToRoute($URLStr, Request $request)
	{
		if (empty($URLStr) || !$this->isURLRewriteEnabled)
		{
			return false;
		}

		foreach ($this->routeList as $route)
		{
			if (preg_match("/^" . $route->getRecognitionPattern() . "$/", $URLStr))
			{
				$URLParams = explode("/", $URLStr);
				$definitionPattern = $route->getDefinitionPattern();
				$definitionParts = explode("/", $definitionPattern);
				foreach ($definitionParts as $index => $part)
				{
					if ($route->isParam($part))
					{
						$paramName = substr($part, 1);
						$value = $URLParams[$index];
						$request->setValue($paramName, $value);
					}
				}
				$requestValueAssigments = $route->getRequestValueAssigments();
				foreach ($requestValueAssigments as $paramName => $value)
				{
					$request->setValue($paramName, $value);
				}
				return $route;
			}
		}
	}
	
	/**
	 * Creates an URL by using a supplied URL param list
	 *
	 * @param array $URLParamList
	 * @return string
	 */
	public function createURL($URLParamList)
	{
		if (!$this->isURLRewriteEnabled)
		{
			return $this->createQueryString($URLParamList);
		}
		if (!empty($URLParamList['controller']) 
		           && $URLParamList['controller'] == self::$defaultController 
		           && (empty($URLParamList['action']) || $URLParamList['action'] == self::$defaultAction) 
		           && sizeof($URLParamList) <= 2)
		{
			return $this->getBaseDir();
		}
		
		$matchingRoute = null;
		foreach ($this->routeList as $route)
		{
			$routeExpectedParamList = $route->getParamList();
			$routeParamsMatch = false;
			
			$routeParamNames = array_keys($routeExpectedParamList);
			$urlParamNames = array_keys($URLParamList);
			
			$urlParamDiff = array_diff($routeParamNames, $urlParamNames);
			$routeParamDiff = array_diff($urlParamNames, $routeParamNames);
			
			if ((sizeof($urlParamDiff)) == 0 && (sizeof($routeParamDiff) == 0))
			{
				foreach ($routeExpectedParamList as $paramName => $paramRequirement)
				{
					if (empty($URLParamList[$paramName]) || !preg_match('/^' . $paramRequirement . '/' , $URLParamList[$paramName]))
					{
						$routeParamsMatch = false;
						break;
					}
					else
					{
						$routeParamsMatch = true;
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
		return $this->getBaseDir() . $url;
	}
	
	private function createQueryString($URLParamList)
	{
		$assigmentList = array();
		foreach ($URLParamList as $paramName => $value)
		{
			$assigmentList[] = $paramName . "=" . urlencode($value);
		}
		return "?" . implode("&", $assigmentList);
	}
	
	public function setURLRewrite($status = true)
	{
		$this->isURLRewriteEnabled = $status;
	}
	
	public function isURLRewriteEnabled()
	{
		return $this->isURLRewriteEnabled;
	}
	
	public function getRequestedRoute()
	{
		return $_GET['route'];
	}
}

/////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////

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

require_once("Request.php");
$router = Router::getInstance();
$request = new Request();

$router->connect(":controller", array("action" => "index"));
$router->connect(":controller/:action");
$router->connect(":controller/:action/:id", array(), array("id" => "[0-9]+"));
$router->connect(":controller/:action/:mode/:id", array(), array("id" => "[0-9]+", "mode" => "create|modify"));

$mappedRoute = $router->mapToRoute("backend.category", $request);
echo "\n<pre>"; print_r($request); echo "</pre>\n";

echo $router->createURL(array("controller" => "backend.category", "action" => "index")); echo "\n";
echo $router->createURL(array("controller" => "filter", "action" => "modify", "id" => "2231")); echo "\n";
echo $router->createURL(array("controller" => "index")); echo "\n";

echo "\n<pre>"; print_r($router); echo "</pre>\n";
echo $router->getBaseDir();
echo $router->getBaseUrl();

//var_dump(preg_match("/^[a-zA-Z\.]+$/", 'backend.controller'));
//preg_match('/^((\w)\.){0,1}(\w)+\/{0,1}(\w)+/', "backend.controller/action/id/");

?>