<?php

ClassLoader::import("framework.ApplicationException");

/**
 * Thrown when controller specified in request does not exists.
 *
 * @package	framework
 * @author Integry Systems
 */
class ControllerNotFoundException extends ApplicationException
{
	/**
	 * Name of controller that was not found
	 */
	private $controllerName;

	/**
	 * @param string $controller Controllers name
	 */
	public function __construct($controllerName)
	{
		parent::__construct("Specified controller ($controllerName) does not exist");
		$this->controllerName = $controllerName;
	}

	/**
	 * Gets a controller name
	 *
	 * @return string Controller name
	 */
	public function getControllerName()
	{
		return $this->controllerName;
	}
}

?>
