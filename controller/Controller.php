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
 * @author Saulius Rupainis <saulius@integry.net>
 */
abstract class Controller
{
	/**
	 * Default action name
	 */
	const DEFAULT_ACTION = 'index';

	/**
	 * Intance of request that can be accessed by actions
	 *
	 * @var Request
	 */
	protected $request = null;

	/**
	 * Controller layout name
	 *
	 * @var string
	 */
	protected $layout = "";

	private $blockList = array();

	/**
	 * Controller constructor
	 *
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
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
	 * Executes controller action (method)
	 *
	 * @param string $actionName
	 * @return Response
	 */
	public final function execute($actionName)
	{
		try
		{
			$this->init();
		}
		catch(ControllerInterruptException $e)
		{
			return $e->createActionRedirectResponse();
		}
		if (method_exists($this, $actionName) && $this->isAction($actionName))
		{
			$response = call_user_func(array(&$this, $actionName));
			if ($response instanceof Response)
			{
				return $response;
			}
			else
			{
				return new RawResponse();
			}
		}
		else
		{
			throw new ActionNotFoundException(get_class($this), $actionName);
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

	public function init()
	{
		return ;
	}

	/**
	 * Gets a structure of the layout that you created
	 *
	 * @return array
	 */
	public final function getLayoutStructure()
	{
		$structure = array();
		foreach($this->blockList as $value)
		{
			$response = call_user_func(array($this, $value['block']));
			if ($response != null && $response instanceof ActionResponse)
			{
				$structure[] = array('container' => $value['container'], 'response' => $response, 'view' => $value['view'], 'name' => $value['block']);
			}
		}
		return $structure;
	}

	/**
	 * Adds a block to a controller layout
	 *
	 * Layout block is an atomic part of application which cannot be called directly
	 * by a user. Block fills a layput which encapsulates currently executed action
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
	protected final function addBlock($containerName, $blockName, $viewName = null)
	{
		$blockMethodName = $blockName."Block";

		if (empty($viewName))
		{
			$viewPath = "block".DIRECTORY_SEPARATOR.$blockName.".tpl";
		}
		else
		{
			$viewPath = $viewName.".tpl";
		}
		if (!method_exists($this, $blockMethodName))
		{
			throw new ControllerException("Block $blockName not found!");
		}
		$this->blockList[] = array("container" => $containerName, "block" => $blockMethodName, "view" => $viewPath);
	}

	public final function getBlockList()
	{
		return $this->blockList;
	}

	/**
	 * Removes a block from a layout so it is not rendered during action axecution
	 *
	 * @param string $blockName
	 */
	protected final function removeBlock($blockName)
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
