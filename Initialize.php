<?php

/**
 * Integry framework bootstrap
 *
 * @package framework
 * @author Integry Systems
 *
 */

require_once(dirname(__file__) . DIRECTORY_SEPARATOR . 'ClassLoader.php');

// we're assuming that application root is one level above the framework directory
// if it's not the case, call ClassLoader::mountPath('.', '/path/to/the/root/directory');
ClassLoader::mountPath('.', dirname(dirname(__file__)) . DIRECTORY_SEPARATOR);

ClassLoader::mountPath('framework', dirname(__file__) . DIRECTORY_SEPARATOR);

ClassLoader::import('framework.request.*');
ClassLoader::import('framework.renderer.*');
ClassLoader::import('framework.response.*');
ClassLoader::import('framework.controller.*');

ClassLoader::import('framework.Application');

$app = new Application();

// initialize default routing rules
$router = $app->getRouter();

$rules = array(
			array(":controller", array("action" => "index"), array()),
			array(":controller/:id", array("action" => "index"), array("id" => "-?[0-9]+")),
			array(":controller/:action", array(), array()),
			array(":controller/:action/:id", array(), array("id" => "-?[0-9]+")),
		);

foreach ($rules as $route)
{
	$router->connect($route[0], $route[1], $route[2]);
}

return $app;

?>