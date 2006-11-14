<?php

ClassLoader::import('framework.response.RedirectResponse');

/**
 * Class for creating action redirect response.
 *
 * Action redirect response indicates a controler and action to redirect to.
 * Redirection is performed by an Application after an instance is returned from an action
 *
 * @see Application
 * @package	framework.response
 * @author Saulius Rupainis
 */
class ActionRedirectResponse extends RedirectResponse
{
	/**
	 * Controllers name to redirect to
	 */
	private $controllerName = "";

	/**
	 * Action name to redirect to
	 */
	private $actionName = "";

	/**
	 * Array of parameters
	 */
	private $paramList = array();

	/**
	 * @param string $content Content of response (optional)
	 */
	public function __construct($controllerName, $actionName, $paramList = array())
	{
		parent::__construct('');

		$this->setControllerName($controllerName);
		$this->setActionName($actionName);
		$this->setParamList($paramList);
	}

	/**
	 * Sets controller to redirect to
	 *
	 * @param string $controller Controller
	 * @return void
	 */
	public function setControllerName($controller)
	{
		$this->controllerName = $controller;
	}

	/**
	 * Gets controller to redirect to
	 *
	 * @return mixed null if no controller set, string otherwise
	 */
	public function getControllerName()
	{
		return $this->controllerName;
	}

	/**
	 * Sets action to redirect to
	 *
	 * @param string $action Action
	 * @return void
	 */
	public function setActionName($action)
	{
		$this->actionName = $action;
	}

	/**
	 * Gets action to redirect to
	 *
	 * @return mixed null if no action set, string otherwise
	 */
	public function getActionName()
	{
		return $this->actionName;
	}

	/**
	 * Sets parameters
	 *
	 * @param array $parameter Associative array with parameters
	 * @return void
	 */
	public function setParamList($paramList)
	{
		$this->paramList = $paramList;
	}

	/**
	 * Gets parameters
	 *
	 * @return array Associative array with paramaters
	 */
	public function getParamList()
	{
		return (array)$this->paramList;
	}
}

?>