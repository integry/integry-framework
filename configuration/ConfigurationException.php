<?php

ClassLoader::import("framework.ApplicationException");

/**
 * Signals when configuration errors occurs
 *
 * @package framework.configuration
 * @author Saulius Rupainis <saulius@integry.net>
 */
class ConfigurationException extends ApplicationException {
}

?>