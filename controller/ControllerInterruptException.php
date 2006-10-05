<?php


/**
 * Controller interrupt exception
 * 
 * @package framework.controller
 * @author Saulius Rupainis
 */
class ControllerInterruptException extends ControllerException {
	
	private $controllerName = "";
	private $actionName = "";
	
	public function __construct($controllerNameToCall, $actionNameToCall) {
		parent::__construct();
		$this->controllerName = $controllerNameToCall;
		$this->actionName = $actionNameToCall; 
	}
	
	public function createActionRedirectResponse() {
		return new ActionRedirectResponse($this->controllerName, $this->actionName);
	}
}

?>