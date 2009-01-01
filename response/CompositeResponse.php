<?php

ClassLoader::import('framework.response.ActionResponse');

/**
 * Response for carying instructions to an application to render some other action(s)
 *
 * @package	framework.response
 * @author	Integry Systems
 */
abstract class CompositeResponse extends Response
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

	private $responseList = array();

	/**
	 * Includes action output to response
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

	public function addResponse($actionOutputHandle, Response $response, Controller $controller, $actionName)
	{
		$this->responseList[$actionOutputHandle] = array($response, $controller, $actionName);
	}

	public function getResponseList()
	{
		return $this->responseList;
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

	public function setResponse($outputHandle, Response $response)
	{
		$this->set($outputHandle, $response->getData());
	}
}

?>
