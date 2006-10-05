<?php

/**
 * Router config compiler
 * 
 * Parses routes defined as XML and compiles it to a plain PHP code
 * Based on simpleXML parsed
 * 
 * @package framework.configuration.route
 * @author Saulius Rupainis <saulius@integry.net>
 */
class RouteCompiler {
	
	/**
	 * The list of routing configs (full path of file)
	 *
	 * @var string[]
	 */
	private $configList = array();
	
	/**
	 * Recursively reads and registers all files in a directory which will be compiled
	 *
	 * @param string $dirPath
	 */
	public function loadRouteConfigList($dirPath) {
		if ($dirHandle = opendir($dirPath)) {
			while (false !== ($file = readdir($dirHandle))) {
				if ($file != "." && $file != ".." && !is_dir($file)) {
		           $this->addRouteConfigPath($dirPath . DIRECTORY_SEPARATOR . $file);
				}
			}
			closedir($dirHandle);
		}
	}
	
	/**
	 * Compiles a route described in an XML file by converting it to a PHP code
	 *
	 * @param string $routeFile File path
	 * @return string PHP code
	 * @see Router
	 */
	public function compileRouteConfig($routeFile) {
		$routerCfg = simplexml_load_file($routeFile);
		$compiledCode = "";
		
		foreach ($routerCfg as $route) {
			$routePattern = $route['pattern'];
		
			$requirements = array();
			$translateTo = array();
			
			foreach ($route->assign->children() as $variable) {	
				$translateTo[] = "'" . $variable['name'] . "' => " . "'" . $variable['value'] . "'"; 
			}
			
			foreach ($route->require->children() as $token) {
				$requirements[] = "'" . $token['name'] . "' => " . "'" . $token['pattern'] . "'"; 
			}
			$translateToCode = "array(" . implode(", ", $translateTo) . ")";
			$requirementsCode = "array(" . implode(", ", $requirements) . ")";
			$compiledCode .= "Router::connect('$routePattern', $translateToCode, $requirementsCode);\n";
		}
		
		return $compiledCode;
	}
	
	/**
	 * Compiles all routing files 
	 *
	 * @return string Compiled PHP code
	 */
	public function compile() {
		$PHPCode = "";
		foreach ($this->configList as $filePath) {
			$PHPCode .= @$this->compileRouteConfig($filePath);
		}
		return $PHPCode;
	}
	
	/**
	 * Registers a route config
	 *
	 * @param string $routeFile
	 */
	public function addRouteConfigPath($routeFile) {
		$this->configList[] = $routeFile;
	}
}

?>