<?php

/**
 * ActionNotFoundException exception.
 *
 * Thrown when a requested action (controller method) does not exist.
 *
 * @package	framework.controller
 */
class ActionNotFoundException extends ControllerException
{
	/**
	 * Controller name that contains the action
	 */
	private $controllerName;

	/**
	 * Action name
	 */
	private $actionName;

	/**
	 * @param string $controllerName
	 * @param string $actionName
	 */
	public function __construct($controllerName, $actionName)
	{
		parent::__construct("Specified action does not exist ($controllerName.$actionName)");

		$this->controllerName = $controllerName;
		$this->actionName = $actionName;
	}

	/**
	 * Gets controller name
	 *
	 * @return string Controller name
	 */
	public function getControllerName()
	{
		return $this->controllerName;
	}

	/**
	 * Gets action name
	 *
	 * @return string Action name
	 */
	public function getActionName()
	{
		return $this->actionName;
	}
}

?>
