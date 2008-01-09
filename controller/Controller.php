<?php

/**
 * Class of application controller (action container)
 *
 * A controller is a modular part of web application responsible for a certain
 * class of actions which are contained as methods.
 *
 * Public method of derived controller is called an action and it is the smallest
 * (atomic) part of an application. Actions can share a helper methods (methods
 * defined in a derived controller and having a "private" access level) to perform
 * common tasks.
 *
 * Note: when action does not return anything it is considered that a RawResponse is returned
 *
 * @package	framework.controller
 * @author Integry Systems
 */
abstract class Controller
{
	/**
	 * Default action name
	 */
	const DEFAULT_ACTION = 'index';

	/**
	 * Instance of request that can be accessed by actions
	 *
	 * @var Request
	 */
	protected $request = null;

	/**
	 * Application instance
	 *
	 * @var LiveCart
	 */
	protected $application = null;

	/**
	 * Controller layout name
	 *
	 * @var string
	 */
	protected $layout = "";

	private $blockList = array();

	private $controllerName;

	/**
	 * @var Controller
	 */
	private static $currentController;

	/**
	 * Controller constructor
	 *
	 * @param Request $request
	 */
	public function __construct(Application $application)
	{
		$this->application = $application;
		$this->request = $this->application->getRequest();
		self::$currentController = $this;
	}

	public static function getCurrentController()
	{
		return self::$currentController;
	}

	/**
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Default application controller action. It is called when no action name is supplied
	 * for a web application
	 *
	 * @return Response
	 */
	public function index()
	{
		return ;
	}

	/**
	 * Executes controller action (method) and gets a response instance
	 *
	 * Registers request data array if request is renderable
	 *
	 * @param string $actionName
	 * @return Response
	 */
	public final function execute($actionName)
	{
		$result = $this->init();
		if ($result instanceof Response)
		{
			return $result;
		}

		if (method_exists($this, $actionName) && $this->isAction($actionName))
		{
			$response = call_user_func(array(&$this, $actionName));
			if ($response instanceof Response)
			{
				if ($response instanceof ActionResponse)
				{
					$response->set("request", $this->request->toArray());
				}
				return $response;
			}
			else
			{
				return new RawResponse();
			}
		}
		else
		{
			throw new ActionNotFoundException($this);
		}
	}

	/**
	 * Checks if a method "$actionName" is really an action
	 * An action is considered to be a public method of controller defined in a top-level
	 * derived class
	 *
	 * @param string $actionName
	 */
	private final function isAction($actionName)
	{
		$thisControllerName = get_class($this);
		$controllerMethod = new ReflectionMethod($thisControllerName, $actionName);
		$reflectionClass = $controllerMethod->getDeclaringClass();
		if ($thisControllerName != $reflectionClass->getName() || !$controllerMethod->isPublic())
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Sets layout template name (without .tpl file extension)
	 * Layout templates are located in applications view/layout directory
	 *
	 * @param string $layoutTemplatePath
	 */
	public final function setLayout($layoutTemplatePath)
	{
		$this->layout = $layoutTemplatePath;
	}

	public final function removeLayout()
	{
		$this->layout = null;
	}

	/**
	 * Gets a layout name
	 *
	 * @return string
	 */
	public final function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Implement this method for dynamic layout organization.
	 *
	 * Dynamic layout organization is turning on or of some contextual application blocks
	 * You can do this by calling folowing methods:
	 * self::addBlock()
	 * self::removeBlock()
	 *
	 * @return void;
	 */
	//abstract public function prepareLayout();

	protected function init()
	{
		return ;
	}

	/**
	 * Gets a structure of the layout that you created
	 *
	 * @return array
	 */
	public function getLayoutStructure()
	{
		$structure = array();
		foreach($this->blockList as $value)
		{
			$response = call_user_func($value['call']);
			if ($response != null && $response instanceof ActionResponse)
			{
				$structure[] = array('container' => $value['container'], 'response' => $response, 'view' => $value['view'], 'name' => $value['block']);
			}
		}
		return $structure;
	}

	/**
	 *  Set controller identification string
	 *
	 *  The controller name is used to retrieve resources that are directly associated to a particular
	 *  controller, for example, view templates.
	 */
	public final function setControllerName($name)
	{
		$this->controllerName = $name;
	}

	/**
	 *  Get controller identification string
	 */
	public final function getControllerName()
	{
		return $this->controllerName;
	}

	/**
	 * Adds a block to a controller layout
	 *
	 * Layout block is an atomic part of application which cannot be called directly
	 * by a user. Block fills a layout which encapsulates currently executed action
	 * (some kind of contextual menu or enviroinment depending on application part)
	 *
	 * Usage (lets say it is a body of some controller action)
	 * <code>
	 *
	 * // Generates output of newsListBlock() by using block/newList.tpl template (by default)
	 * // and assigns an output to an APPLICATION_NAV template variable.
	 *
	 * $this->addBlock("APPLICATION_NAV", "newsList");
	 *
	 * // Lets say this is some other action
	 * $this->removeBlock("newsList");
	 *
	 * </code>
	 *
	 * @param string $containerName Name of template variable to which content of this block will be assigned
	 * @param string $blockName Name of block
	 * @param string $viewName View name for block (without file extension)
	 *
	 * @see self::removeBlock()
	 */
	public final function addBlock($containerName, $blockName, $viewName = null)
	{
		if (!is_array($blockName))
		{
			$blockName = array($this, $blockName);
		}

		$blockMethodName = $blockName[1] . 'Block';

		if (empty($viewName))
		{
			$viewPath = "block" . DIRECTORY_SEPARATOR . $blockName[1] . ".tpl";
		}
		else
		{
			$viewPath = $viewName . ".tpl";
		}

		if (!method_exists($blockName[0], $blockMethodName))
		{
			throw new ControllerException($this, "Block $blockName[1] not found!");
		}

		$this->blockList[] = array("container" => $containerName, "block" => $blockMethodName, "view" => $viewPath, 'call' => array($blockName[0], $blockMethodName));
	}

	/**
	 * Removes a block from a layout so it is not rendered during action axecution
	 *
	 * @param string $blockName
	 */
	public final function removeBlock($blockName)
	{
		$blockMethodName = $blockName."Block";
		foreach($this->blockList as $index => $block)
		{
			$position = array_search($blockMethodName, $block);
			if ($position !== false)
			{
				unset($this->blockList[$index]);

				return true;
			}
		}
	}

}

?>
