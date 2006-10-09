<?php

ClassLoader::import('framework.response.ActionResponse');

/**
 * Response for carying instructions to an application to render some other action(s)
 *
 * @package	framework.response
 */
class CompositeResponse extends ActionResponse
{
	/**
	 * Controller variable name
	 */
	const CONTROLLER_HANDLE = 'controller';

	/**
	 * Action variable name
	 */
	const ACTION_HANDLE = 'action';

	/**
	 * Stores includes
	 */
	private $requestedActionList = array();

	/**
	 * Includes onothers action output to response
	 *
	 * @param string $name Name of value assign to
	 * @param string $controller Name of controller
	 * @param string $action Name of action
	 * @return void
	 */
	public function addAction($actionOutputHandle, $controllerName, $actionName)
	{
		$this->requestedActionList[$actionOutputHandle] = array(self::CONTROLLER_HANDLE => $controllerName, 
                                                            self::ACTION_HANDLE => $actionName);
	}

	/**
	 * Gets includes names
	 *
	 * @return array with names
	 */
	public function getRequestedActionList()
	{
		return $this->requestedActionList;
	}

	/**
	 * Gets contoller name for include
	 *
	 * @param string $name Name of include
	 * @return mixed null if there is no include, otherwise string - Name of controller
	 */
	public function getControllerName($actionOutputHandle)
	{
		if (isset($this->requestedActionList[$actionOutputHandle]))
		{
			return $this->requestedActionList[$actionOutputHandle][self::CONTROLLER_HANDLE];
		}
		return null;
	}

	/**
	 * Gets action name for include
	 *
	 * @param string $name Name of include
	 * @return mixed null if there is no include, otherwise string - Name of action
	 */
	public function getIncludeAction($actionOutputHandle)
	{
		if (isset($this->requestedActionList[$actionOutputHandle]))
		{
			return $this->requestedActionList[$actionOutputHandle][self::ACTION_HANDLE];
		}
		return null;
	}
}

?>
