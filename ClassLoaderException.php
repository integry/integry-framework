<?php

ClassLoader::import("framework.ApplicationException");

/**
 * Indicates that class loader failed loading some source file
 *
 * @package framework
 * @author Integry Systems
 */
class ClassLoaderException extends ApplicationException
{
}

?>
