<?php

/**
 * Controller level exception.
 *
 * @author	Saulius Rupainis <saulius.rupainis@artogama.lt>
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
