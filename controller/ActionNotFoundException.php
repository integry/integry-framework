<?php

/**
 * ActionNotFoundException exception.
 *
 * Thrown when a requested action (controller method) does not exist.
 *
 * @package	framework.controller
 */
class ActionNotFoundException extends HTTPStatusException
{
	const STATUS_CODE = 404;

	public function __construct(Controller $controller, $message = false)
	{
	    parent::__construct($controller, self::STATUS_CODE, $message);
	}
}

?>
