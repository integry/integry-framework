<?php

ClassLoader::import("framework.ApplicationException");

/**
 * Exception that indicates an attempt to execute a restricted controller/action
 *
 * @package framework.controller.exception
 * @author Integry Systems
 */
class UnauthorizedException extends HTTPStatusException
{
	const STATUS_CODE = 401;

	public function __construct(Controller $controller, $message = false)
	{
		parent::__construct($controller, self::STATUS_CODE, $message);
	}
}

?>