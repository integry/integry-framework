<?php

ClassLoader::import("framework.request.Request");
ClassLoader::import("framework.request.Route");

class Router
{
	/**
	 * Router instance (sigleton)
	 *
	 * @var Router
	 */
	private static $instance = null;

	public static $baseDir = "";
	public static $defaultController = "index";
	public static $defaultAction = "index";

	/**
	 * The list of defined routes
	 *
	 * @var Route[]
	 */
	private $routeList = array();
	private $isURLRewriteEnabled = true;

	public static function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new Router();
		}
		return self::$instance;
	}


	/*
	public function setBaseDir($dir) {

	$this->baseDir = $dir;
	}

	public function getBaseDir() {

	return $this->baseDir;
	}
	 */

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
	 * @param string $URLPattern
	 * @param array $defaultValueList
	 * @param array $varRequirements
	 *
	 */
	public function connect($URLPattern, $defaultValueList = array(), $varRequirements = array())
	{
		if (substr($URLPattern, 0, 1) == "/")
		{
			$URLPattern = substr($URLPattern, 1);
		}
		$this->routeList[] = new Route($URLPattern, $defaultValueList, $varRequirements);
	}

	/**
	 * Parses route pattern (request value supplied by URL) and sets appropriate
	 * values to a Request instance
	 *
	 * @param string $URL
	 * @param Request $request
	 * @return Route
	 */
	public function mapToRoute($URL, Request $request)
	{
		if (empty($URL))
		{
			$request->setValue("action", self::$defaultAction);
			$request->setValue("controller", self::$defaultController);
			return true;
		}
		$URLVariables = explode("/", $URL);
		foreach($this->routeList as $route)
		{
			if (preg_match($route->getRegexpPattern(), $URL))
			{
				if ($route->hasTokens())
				{
					foreach($route->getVariableList()as $name => $value)
					{
						$request->setValue($name, $value);
					}
					foreach($route->getTokenList()as $index => $token)
					{
						if ($token != null && !empty($URLVariables[$index]))
						{
							$request->setValue($token, $URLVariables[$index]);
						}
					}
				}

				return $route;
			}
		}
		throw new RouterException("No route defined for this URL pattern");
	}

	/**
	 * Informs Router that mod_rewrite is enabled so URL's will be generatated without
	 * "frontcontroller.php?route=" part
	 *
	 */
	public function enableURLRewrite()
	{
		$this->isURLRewriteEnabled = true;
	}

	/**
	 * Informs router that URL rewriting (mod_rewrite) is disabled
	 *
	 */
	public function diableURLRewrite()
	{
		$this->isURLRewriteEnabled = false;
	}

	public function getBaseDir()
	{
		return self::$baseDir;
	}

	/**
	 * Creates an URL by using supplied request values
	 *
	 * @param array $requestParamList
	 * @return string
	 */
	public function createURL($requestParamList = array())
	{
		if (empty($requestParamList['action']))
		{
			$requestParamList['action'] = self::$defaultAction;
		}
		if (empty($requestParamList['controller']))
		{
			$requestParamList['controller'] = self::$defaultController;
		}
		$actionName = $requestParamList['action'];
		$controllerName = $requestParamList['controller'];

		foreach($this->routeList as $route)
		{

			if (($route->getAction() == null || $route->getAction() == $actionName) && ($route->getController() == null || $route->getController() == $controllerName))
			{

				$URL = $route->getURLPattern();
				foreach($route->getTokenList()as $index => $value)
				{
					if (!empty($value))
					{
						$URL = str_replace(":".$value, @$requestParamList[$value], $URL);
					}
				}

				if ($this->isURLRewriteEnabled)
				{
					$pos = @strpos($_SERVER['REQUEST_URI'], $this->getRequestPath());
					$URLStart = substr($_SERVER['REQUEST_URI'], 0, $pos);
					return $URLStart.$URL;
				}
				else
				{
					return "?".$URL;
				}
			}
		}
		throw new RouterException("Unable to map to any route");
	}

	/**
	 * Gets a request value containing route
	 *
	 * @return unknown
	 */
	public function getRequestPath()
	{
		return $_GET['route'];
	}
}

?>
