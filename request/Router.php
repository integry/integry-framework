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
	 * Request object instance
	 *
	 * @var Request
	 */
	private $request;

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
	private $defaultController = "index";

	/**
	 * Default action name
	 *
	 * @var string
	 */
	private $defaultAction = "index";

	/**
	 * Application base dir
	 * E.x. www.example.com/myapplication/dir/ base dir is /myapplication/dir/
	 *
	 * @var string
	 */
	private $baseDir = "";

	/**
	 * Base url
	 *
	 * @var string
	 */
	private $baseUrl = "";

	/**
	 * https Base url
	 *
	 * @var string
	 */
	private $httpsBaseUrl = "";

	private $isHttps = false;

	private $urlScheme = 'http://';

	private $variableSeparator = '&amp;';

	/**
	 * Identifies if mod_rewrite is enabled
	 * Should be set manually by using enableURLRewrite(flag)
	 *
	 * @var bool
	 */
	private $isURLRewriteEnabled = true;

	private $virtualBaseDir;

	/**
	 * Custom return route
	 *
	 * @var string
	 */
	private $returnPath;

	private $autoAppendVariableList = array();

	private $autoAppendQueryVariableList = array();

	private $sslActions = array();

	private $sslHost = '';

	/**
	 * Router constructor
	 *
	 * @todo Add port to baseUrl
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;

		if (!empty($_SERVER['HTTPS']) && 'off' != $_SERVER['HTTPS'])
		{
			$this->urlScheme = 'https://';
			$this->isHttps = true;
		}

		$this->baseDir = dirname($_SERVER['PHP_SELF']);
		if (strlen($this->baseDir) > 1)
		{
			$this->baseDir .= '/';
		}

		if (isset($_SERVER['HTTP_HOST']))
		{
			$this->baseUrl = $this->urlScheme . $_SERVER['HTTP_HOST'] . $this->baseDir;
			$this->httpsBaseUrl = 'https://' . $_SERVER['HTTP_HOST'] . $this->baseDir;
			$this->getBaseDirFromUrl();
		}
	}

	/**
	 * Gets a base url
	 *
	 * @return string
	 */
	public function getBaseDir()
	{
		return $this->baseDir;
	}

	public function getBaseDirFromUrl()
	{
		if (!$this->virtualBaseDir)
		{
			$URI = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

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

			$this->virtualBaseDir = str_replace($route, "", urldecode($URIBase));

			// strip double slashes
			$this->virtualBaseDir = preg_replace('/\/{2,}$/', '/', $this->virtualBaseDir);
		}

		return $this->virtualBaseDir;
	}

	public function setBaseDir($dir, $virtualBaseDir)
	{
		$this->baseDir = $dir;
		$this->virtualBaseDir = $virtualBaseDir;

		if (!isset($_SERVER['HTTP_HOST']))
		{
			$_SERVER['HTTP_HOST'] = 'localhost';
		}

		$this->baseUrl = $this->urlScheme . $_SERVER['HTTP_HOST'] . $this->baseDir;
		$this->httpsBaseUrl = 'https://' . $_SERVER['HTTP_HOST'] . $this->baseDir;
	}

	/**
	 * Gets a base directory
	 *
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->baseUrl;
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
	 * Routes are evaluated (mapToRoute(), createUrl() functions) in the order they were
	 * added. To add a route to the beginning of the route list, use connectPriority()
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

	public function connectPriority($routeDefinitionPattern, $paramValueAssigments = array(), $paramValueRequirements = array())
	{
		$route = new Route($routeDefinitionPattern, $paramValueAssigments, $paramValueRequirements);
		array_unshift($this->routeListByParamCount[count($route->getParamList())], $route);
		array_unshift($this->routeList, $route);
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
		if ('/' == substr($URLStr, -1))
		{
			$URLStr = substr($URLStr, 0, -1);
		}

		if (empty($URLStr) || !$this->isURLRewriteEnabled)
		{
			if (!$request->isValueSet("action"))
			{
				$request->set("action", $this->defaultAction);
			}
			if (!$request->isValueSet("controller"))
			{
				$request->set("controller", $this->defaultController);
			}
			return false;
		}

		foreach ($this->routeList as $route)
		{
			$routePattern = str_replace('\047', '\/', $route->getRecognitionPattern());
			$routePattern = str_replace('.', '\.', $routePattern);

			if (preg_match("/^" . $routePattern . "$/U", $URLStr, $result))
			{
				unset($result[0]);

				$varList = $route->getVariableList();

				foreach ($varList as $key => $value)
				{
				  	$request->set($value, $result[$key + 1]);
				}

				$requestValueAssigments = $route->getRequestValueAssigments();
				$request->sanitizeArray($requestValueAssigments);
				$request->setValueArray($requestValueAssigments);

				return $route;
			}
		}
		throw new RouterException("Unable to map to any route");
	}

	/**
	 * Creates an URL by using a supplied URL param list
	 *
	 * @param array $URLParamList
	 * @param bool $isXHtml		Generate XHTML valid URL's (use &amp; as variable separator)
	 * @return string
	 */
	public function createURL($URLParamList, $isXHtml = false)
	{
		$variableSeparator = $isXHtml ? '&amp;' : '&';

		if (isset($URLParamList['']))
		{
			unset($URLParamList['']);
		}

		if (!isset($URLParamList['controller']))
		{
			$URLParamList['controller'] = $this->defaultController;
		}

		if (!isset($URLParamList['action']))
		{
			$URLParamList['action'] = $this->defaultAction;
		}

		if (empty($URLParamList['query']))
		{
			unset($URLParamList['query']);
		}

		$queryVars = array();
		if (!empty($URLParamList['query']) && !is_array($URLParamList['query']))
		{
			foreach (explode('&', $URLParamList['query']) as $val)
			{
				list($key, $value) = explode('=', $val, 2);
				$queryVars[urldecode($key)] = urldecode($value);
			}
		}

		if (isset($URLParamList['query']) && is_array($URLParamList['query']))
		{
			$queryVars = $URLParamList['query'];
		}

		unset($URLParamList['query']);

		// merging persisted variables into an URL variable array
		$URLParamList = array_merge($this->autoAppendVariableList, $URLParamList);

		$queryVars = array_merge($this->autoAppendQueryVariableList, $queryVars);
		$queryVars = array_diff_key($queryVars, $URLParamList);

		$addReturnPath = false;
		if (!empty($URLParamList['returnPath']))
		{
			$addReturnPath = true;
			unset($URLParamList['returnPath']);
		}

		$queryToAppend = '';
		if (!empty($queryVars))
		{
			$pairs = array();
			foreach ($queryVars as $key => $value)
			{
				$pairs[] = urlencode($key) . '=' . urlencode($value);
			}

			$queryToAppend = implode($variableSeparator, $pairs);
			$queryToAppend = ((strpos($this->virtualBaseDir, '?') === false) ? '?' : '&') . $queryToAppend;
		}

		/* Handling special case: URL rewrite is not enabled */
		if (!$this->isURLRewriteEnabled)
		{
			return $this->createQueryString($URLParamList) . $variableSeparator . substr($queryToAppend, 1);
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

		if ($this->isSsl($URLParamList['controller'], $URLParamList['action']))
		{
			$url = $this->createFullUrl($url, true);
		}

		if ($addReturnPath)
		{
			$url = $this->setUrlQueryParam($url, 'return', $this->getReturnPath());
		}

		return strip_tags($url);
	}

	public function createFullUrl($relativeUrl, $https = null)
	{
		if (preg_match('/^http[s]{0,1}:\/\//i', $relativeUrl))
		{
			return $relativeUrl;
		}

		$parts = parse_url($https ? $this->httpsBaseUrl : $this->baseUrl);

		if (false === $https)
		{
			$parts['scheme'] = 'http';
		}

		if (('https' == $parts['scheme']) && $this->sslHost)
		{
			$parts['host'] = $this->sslHost;
		}

		return $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '') . $relativeUrl;
	}

	public function setReturnPath($returnRoute)
	{
		$this->returnPath = $returnRoute;
	}

	public function setVariableSeparator($separator)
	{
		$this->variableSeparator = $separator;
	}

	private function getReturnPath()
	{
		return $this->returnPath ? $this->returnPath : $this->getRequestedRoute();
	}

	private function findRoute($URLParamList)
	{
		$urlParamNames = array_keys($URLParamList);

		$matchingRoute = null;

		foreach ($this->routeListByParamCount[count($urlParamNames)] as $route)
		{
			$routeExpectedParamList = $route->getParamList();

			if (!array_diff($urlParamNames, array_keys($routeExpectedParamList)))
			{
				foreach ($routeExpectedParamList as $paramName => $paramRequirement)
				{
					if (!preg_match('/^' . $paramRequirement . '/U' , $URLParamList[$paramName]))
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
				  	return $matchingRoute;
				}
			}
		}
	}

	private function createQueryString($URLParamList)
	{
		$assigmentList = array();
		foreach ($URLParamList as $paramName => $value)
		{
			$assigmentList[] = $paramName . "=" . urlencode($value);
		}

		return ((strpos($this->virtualBaseDir, '?') === false) ? '?' : '&') . implode("&", $assigmentList);
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
		return strip_tags($this->request->get('route', null));
	}

	public function setRequestedRoute($route)
	{
		$route = strip_tags($route);
		$this->request->set('route', $route);
	}

	/**
	 *  @param string $url Relative URL
	 */
	public function getRouteFromUrl($url)
	{
		$route = substr(strip_tags($url), strlen($this->getBaseDirFromUrl()));
		if (strpos($route, '?'))
		{
			$route = substr($route, 0, strpos($route, '?'));
		}

		return $route;
	}

	public function createUrlFromRoute($route, $isXHtml = false)
	{
		$variableSeparator = $isXHtml ? '&amp;' : '&';

		$query = implode($variableSeparator, array_keys($this->autoAppendQueryVariableList));
		if ($query)
		{
			$query = '?' . $query;
		}

		$url = $this->getBaseDirFromUrl() . $route . $query;
		return strip_tags($url);
	}

	/**
	 *	A helper function for manipulating URL query parameters
	 */
	public function setUrlQueryParam($url, $param, $paramValue)
	{
		$parts = explode('?', $url, 2);
		$params = array();
		if (isset($parts[1]))
		{
			$pairs = explode($this->variableSeparator, $parts[1]);
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

		$url = $parts[0] . '?' . implode($this->variableSeparator, $pairs);

		return strip_tags($url);
	}

	/**
	 *	Append all query parameters from the request URL to the supplied URL
	 */
	public function addQueryParams($url)
	{
		if (!$_SERVER['QUERY_STRING'])
		{
			return strip_tags($url);
		}

		$pairs = explode('&', $_SERVER['QUERY_STRING']);
		foreach ($pairs as $pair)
		{
			list($param, $value) = explode('=', $pair, 2);
			if ('route' == $param)
			{
				continue;
			}
			$url = $this->setUrlQueryParam($url, $param, $value);
		}

		return $url;
	}

	/**
	 * Set variable that gets automatically assigned when creating URL
	 * (self::createURL()) (there will be no need to assign such variables
	 * manually. E.x.current language code for a multilingual webapp)
	 *
	 * @param array $assocArray VariableName => VarValue
	 */
	public function setAutoAppendVariables($assocArray)
	{
		$this->autoAppendVariableList = $assocArray;
	}

	public function removeAutoAppendVariable($key)
	{
		unset($this->autoAppendVariableList[$key]);
	}

	/**
	 * Set variable list that will automatically be appended to URL query part
	 * (for example, ?currency=USD). This method should be used when there are no
	 * special routing cases defined for the particular variable.
	 *
	 * @param string $value $key Variable name
	 * @param string $value $value Variable value
	 */
	public function addAutoAppendQueryVariable($key, $value)
	{
		$this->autoAppendQueryVariableList[$key] = $value;
	}

	public function setSslAction($controller = '', $action = '')
	{
		if (!isset($this->sslActions[$controller]))
		{
			$this->sslActions[$controller] = array();
		}

		if ($action)
		{
			$this->sslActions[$controller][$action] = true;
		}
		else
		{
			$this->sslActions[$controller] = array();
		}
	}

	public function setSslHost($hostName)
	{
		$this->sslHost = $hostName;
	}

	public function isSSL($controller, $action)
	{
		return

			// all actions are SSL
			isset($this->sslActions[''])

			// the particular action
			|| isset($this->sslActions[$controller][$action])

			// all actions for the particular controller
			|| (isset($this->sslActions[$controller]) && empty($this->sslActions[$controller]));
	}

	public function isHttps()
	{
		return $this->isHttps;
	}
}

?>
