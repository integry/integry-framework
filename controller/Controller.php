<?php

ClassLoader::import('framework.response.BlockResponse');

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

	private $blockName;
	private $parentController;

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
				if ($response instanceof Renderable)
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

	public function executeBlock($methodName)
	{
		return call_user_func(array($this, $methodName));
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

		if (!$controllerMethod->isPublic())
		{
			return false;
		}

		return !(($thisControllerName != $reflectionClass->getName()) && $reflectionClass->isAbstract() && !call_user_func(array($reflectionClass->getName(), 'isCallable')));
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

	public function getBlockResponse(&$block)
	{
		if (!is_object($block['call'][0]))
		{
			$block['call'][0] = $this->application->getControllerInstance($block['call'][0]);
		}

		// pass the name of the current block container
		if ($block['call'][0] instanceof Controller)
		{
			$block['call'][0]->setBlockName($this->getBlockName());
			$block['call'][0]->setParentController($this);
		}

		return $this->application->execute($block['call'][0], $block['call'][1], true);
	}

	/**
	 *	Get blocks by container name
	 */
	public function getBlocks($name)
	{
		$blocks = array();
		foreach($this->blockList as $block)
		{
			if ($block['container'] == $name)
			{
				$blocks[] = $block;
			}
		}

		return $blocks;
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
	 * (some kind of contextual menu or environment depending on application part)
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
	 * @param bool $prepend Whether to add the block to the beginning of the current stack
	 *
	 * @see self::removeBlock()
	 */
	public final function addBlock($containerName, $blockName, $viewName = null, $prepend = false)
	{
		if (!is_array($blockName))
		{
			$blockName = array($this, $blockName);
		}

		$viewExt = $this->application->getRenderer()->getViewExtension();

		$blockMethodName = $blockName[1] . (substr($blockName[1], -5) == 'Block' ? '' : 'Block');

		if (empty($viewName))
		{
			$viewName = "block" . DIRECTORY_SEPARATOR . $blockName[1] . "." . $viewExt;
		}

		$viewPath = $viewName . (substr($viewName, -1 * (strlen($viewExt) + 1)) == '.' . $viewExt ? '' : '.' . $viewExt);

		/*
		if (!method_exists($blockName[0], $blockMethodName))
		{
			throw new ControllerException($this, "Block $blockName[1] not found!");
		}
		*/

		$entry = array("container" => $containerName, "block" => $blockMethodName, "view" => $viewPath, 'call' => array($blockName[0], $blockMethodName));

		if ($prepend)
		{
			array_unshift($this->blockList, $entry);
		}
		else
		{
			$this->blockList[] = $entry;
		}
	}

	/**
	 * Removes a block from a layout so it is not rendered during action axecution
	 *
	 * @param string $blockName
	 */
	public final function removeBlock($blockName)
	{
		$blockMethodName = $blockName . 'Block';

		foreach($this->blockList as $index => $block)
		{
			if (($block['block'] == $blockMethodName) || ($block['container'] == $blockName))
			{
				unset($this->blockList[$index]);
				return true;
			}
		}
	}

	/**
	 *	Set name of the current block context
	 */
	public function setBlockName($name)
	{
		$this->blockName = $name;
	}

	/**
	 *	Set name of the current block context
	 */
	public function getBlockName()
	{
		return $this->blockName;
	}

	public function setParentController(Controller $parent)
	{
		$this->parentController = $parent;
	}

	public function getParentController()
	{
		return $this->parentController;
	}

	public static function isCallable()
	{
		return false;
	}
}

?>
