<?php

ClassLoader::import("framework.ApplicationException");

/**
 * Exception that is triggered when request variable does not meet requirements defined in a Check subclass
 *
 * @package framework.request.validator.check
 * @author Integry Systems
 */
class CheckException extends ApplicationException
{
}

?>