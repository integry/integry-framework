<?php

/**
 * Controller level exception.
 *
 * @author	Integry Systems
 * @package	framework.controller
 */
class ControllerException extends HTTPStatusException
{
	const STATUS_CODE = 404;

	public function __construct(Controller $controller, $message = false)
	{
		parent::__construct($controller, self::STATUS_CODE, $message);
	}
}

?>
