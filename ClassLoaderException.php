<?php

ClassLoader::import("framework.ApplicationException");

/**
 * Indicates that class loader failed loading some source file
 *
 * @package framework
 * @author Saulius Rupainis <saulius@integry.net>
 */
class ClassLoaderException extends ApplicationException
{
}

?>
