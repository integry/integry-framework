<?php

ClassLoader::import("framework.configuration.route.RouteCompiler");

/**
 * Router configuration utility
 *
 * @package framework.configuration.router
 * @author Saulius Rupainis <saulius@integry.net>
 */
class RouteConfigurator {
	
	private static $recompile = true;
	
	/**
	 * Performs a router configuration: collects and compiles a defined list of 
	 * routing config and/or executes a compiled one
	 *
	 */
	public static function run(Router $router) {
		
		$configPath = ClassLoader::getRealPath("application.configuration.route");
		if (self::$recompile) {
			$compiler = new RouteCompiler();
			$compiler->loadRouteConfigList($configPath);
			$PHPCode = "<?php\n" . $compiler->compile() . "?>\n";

			$cachePath = ClassLoader::getRealPath("cache.configuration.route") . "/route_compiled.php";
			file_put_contents($cachePath, $PHPCode);
		}
		ClassLoader::import("cache.configuration.route.route_compiled");
	}
}

?>