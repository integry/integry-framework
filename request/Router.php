<?php

ClassLoader::import("framework.request.Request");
ClassLoader::import("framework.request.Route");

/**
 * Router
 *
 * @author Integry Systems
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
	 * The list of defined routes sorted by param count (for faster lookup)
	 *
	 * @var Route[]
	 */
	private $routeListByParamCount = array();

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
	 * Should be set manually by using enableURLRewrite(flag)
	 *
	 * @var bool
	 */
	private $isURLRewriteEnabled = true;

	private $virtualBaseDir;

	private static $autoAppendVariableList = array();
	
	private static $autoAppendQueryVariableList = array();	

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

	public function getBaseDirFromUrl()
	{
		if (!$this->virtualBaseDir)
		{
			$URI = $_SERVER['REQUEST_URI'];
	
			$queryStartPos = strpos($URI, '?');
			if ($queryStartPos !== false)
			{
				$URIBase = substr($URI, 0, $queryStartPos);
			}
			else
			{
				$URIBase = $URI;
			}
	
			$route = $this->getRequestedRoute();
			$this->virtualBaseDir = str_replace($route, "", $URIBase);			
		}

		return $this->virtualBaseDir;
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
		$route = new Route($routeDefinitionPattern, $paramValueAssigments, $paramValueRequirements);
		$this->routeListByParamCount[count($route->getParamList())][] = $route;
		$this->routeList[] = $route;
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
	 * @param string $URLPattern
	 * @param array $defaultValueList
	 * @param array $varRequirements
	 *
	 */
	public function mapToRoute($URLStr, Request $request)
	{
		if (empty($URLStr) || !$this->isURLRewriteEnabled)
		{
			if (!$request->isValueSet("action"))
			{
				$request->setValue("action", self::$defaultAction);
			}
			if (!$request->isValueSet("controller"))
			{
				$request->setValue("controller", self::$defaultController);
			}
			return false;
		}

		foreach ($this->routeList as $route)
		{
			if (preg_match("/^" . $route->getRecognitionPattern() . "$/", $URLStr, $result))
			{
				unset($result[0]);
				
				$varList = $route->getVariableList();
				
				foreach ($varList as $key => $value)
				{
				  	$request->setValue($value, $result[$key + 1]);
				}
				
				$requestValueAssigments = $route->getRequestValueAssigments();
				foreach ($requestValueAssigments as $paramName => $value)
				{
					$request->setValue($paramName, $value);
				}
				
				return $route;
			}
		}
		throw new RouterException("Unable to map to any route");
	}

	/**
	 * Creates an URL by using a supplied URL param list
	 *
	 * @param array $URLParamList
	 * @return string
	 */
	public function createURL($URLParamList)
	{
		if (!isset($URLParamList['controller']))
		{
			$URLParamList['controller'] = self::$defaultController;
		}

		if (!isset($URLParamList['action']))
		{
			$URLParamList['action'] = self::$defaultAction;
		}

		// merging persisted variables into an URL variable array
		$URLParamList = array_merge(self::$autoAppendVariableList, $URLParamList);

		$queryToAppend = "";
		
		if (self::$autoAppendQueryVariableList)
		{
            $queryVars = implode('&', array_keys(self::$autoAppendQueryVariableList));
    		if (!empty($URLParamList['query']))
    		{
                $URLParamList['query'] .= '&' . $queryVars;                
            }
            else
            {
                $URLParamList['query'] = $queryVars;
            }
        }
		
		$addReturnPath = false;
        if (!empty($URLParamList['returnPath']))
		{
    		$addReturnPath = true;
            unset($URLParamList['returnPath']);
        }
		
		if (!empty($URLParamList['query']))
		{
			if (is_array($URLParamList['query']))
			{
                $pairs = array();
                foreach ($URLParamList['query'] as $key => $value)
                {
                    $pairs[] = urlencode($key) . '=' . urlencode($value);
                }   
                $URLParamList['query'] = implode('&', $pairs);
            }
            
            $queryToAppend = "?" . $URLParamList['query'];
			unset($URLParamList['query']);
		}
		
		/* Handling special case: URL rewrite is not enabled */
		if (!$this->isURLRewriteEnabled)
		{
			return $this->createQueryString($URLParamList) . "&" . substr($queryToAppend, 1);
		}
		/* end */

		/* Handling special case: route to a default controller/action */
		if (($URLParamList['controller'] == self::$defaultController) &&
		    ($URLParamList['action'] == self::$defaultAction))
		{
			return $this->getBaseDirFromUrl() . $queryToAppend;
		}
		/* end */

		$matchingRoute = $this->findRoute($URLParamList);
		
		if ($matchingRoute == null)
		{
			throw new RouterException("Router::createURL - Unable to find matching route <Br />" . 
									  var_export($URLParamList, true));
		}
		
		$url = $matchingRoute->getDefinitionPattern();
		
		$params = array_keys($URLParamList);
		$p = array();
		foreach ($params as $value)
		{
			$p[] = ':' . $value;
		}
		
		$values = array_values($URLParamList);
		$url = str_replace($p, $values, $url);

        $url = $this->getBaseDirFromUrl() . $url . $queryToAppend;

        if ($addReturnPath)
        {
            $url = self::setUrlQueryParam($url, 'return', $this->getRequestedRoute());
        }

		return $url;
	}

	private function findRoute($URLParamList)
	{
		$urlParamNames = array_keys($URLParamList);		
		$urlParamCount = count($urlParamNames);
			
		$matchingRoute = null;
		
		foreach ($this->routeListByParamCount[$urlParamCount] as $route)
		{
			$routeExpectedParamList = $route->getParamList();

			if (!array_diff($urlParamNames, array_keys($routeExpectedParamList)))
			{
				foreach ($routeExpectedParamList as $paramName => $paramRequirement)
				{
					if (!preg_match('/^' . $paramRequirement . '/' , $URLParamList[$paramName]))
					{
						$matchingRoute = null;
						break;
					}
					else
					{
					  	$matchingRoute = $route;
					}
				}
				
				if ($matchingRoute)
				{
				  	break;
				}
				
			}
		}
		
		return $matchingRoute;		
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

	/**
	 *
	 *
	 * @param bool $status
	 */
	public function enableURLRewrite($status = true)
	{
		$this->isURLRewriteEnabled = $status;
	}

	/**
	 * Returns URL rewrite status
	 *
	 * @return bool
	 */
	public function isURLRewriteEnabled()
	{
		return $this->isURLRewriteEnabled;
	}

	/**
	 * Gets a request variable value containing front controllers route
	 *
	 * @return string
	 */
	public function getRequestedRoute()
	{
		if (!empty($_GET['route']))
		{
			return $_GET['route'];
		}
		else
		{
			return null;
		}
	}
	
	public function setRequestedRoute($route)
	{
		$_GET['route'] = $route;	
	}

	public function createUrlFromRoute($route)
	{
		$query = implode('&', array_keys(self::$autoAppendQueryVariableList));
		if ($query)
		{
			$query = '?' . $query;
		}
		
		return $this->getBaseDirFromUrl() . $route . $query;
	}

	/**
	 *	A helper function for manipulating URL query parameters
	 */
	public static function setUrlQueryParam($url, $param, $paramValue)
	{
        $parts = explode('?', $url, 2);
        $params = array();
		if (isset($parts[1]))
        {
			$pairs = explode('&', $parts[1]);
			foreach ($pairs as $pair)
			{
				list($key, $value) = explode('=', $pair, 2);
				$params[$key] = $value;
			}
		}
		
		$params[$param] = $paramValue; 
		
		$pairs = array();
		foreach ($params as $key => $value)
		{
			$pairs[] = $key . '=' . $value;
		}
		
		$url = $parts[0] . '?' . implode('&', $pairs);
		
		return $url;
	}

	/**
	 * Set variable that gets automatically assigned when creating URL
	 * (self::createURL()) (there will be no need to assign such variables
	 * manually. E.x.current language code for a multilingual webapp)
	 *
	 * @param array $assocArray VariableName => VarValue
	 */
	public static function setAutoAppendVariables($assocArray)
	{
		self::$autoAppendVariableList = $assocArray;
	}

	/**
	 * Set variable list that will automatically be appended to URL query part 
     * (for example, ?currency=USD). This method should be used when there are no
     * special routing cases defined for the particular variable.
	 *
	 * @param string $value $key Variable name
	 * @param string $value $value Variable value
	 */
	public static function addAutoAppendQueryVariable($key, $value)
	{
		self::$autoAppendQueryVariableList[$key . '=' . $value] = true;
	}
}

?>