<?php

ClassLoader::import("framework.ApplicationException");

/**
 * Exception that indicates that a resource was not found
 *
 * @package framework.controller.exception
 * @author Integry Systems
 */
class NotFoundException extends HTTPStatusException
{
	const STATUS_CODE = 404;

	public function __construct(Controller $controller, $message = false)
	{
		parent::__construct($controller, self::STATUS_CODE, $message);
	}
}

?>