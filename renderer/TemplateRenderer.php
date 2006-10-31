<?php

ClassLoader::import('framework.renderer.Renderer');

/**
 * Renderer implementation based in Smarty template engine (Smarty wrapper)
 *
 * @package	framework.renderer
 * @author Saulius Rupainis <saulius@integry.net>
 */
class TemplateRenderer extends Renderer
{
	/**
	 * Path of a directory where compiled smarty templates are stored
	 *
	 * @var string
	 */
	private static $compileDir = "";

	/**
	 * Template engine instance
	 *
	 * @var Smarty
	 */
	private $tpl = null;

	private static $smartyInstance = null;

	private $router = null;

	/**
	 * Template renderer constructor
	 *
	 * Creates a smarty instance and sets a compile directory path (this is required
	 * by smarty)
	 */
	public function __construct(Router $router)
	{
		$this->router = $router;
		$this->tpl = self::getSmartyInstance();
		//$this->tpl->register_function("link", array($this, "helperFunctionLinkTo"));

		$this->registerHelperList();
		$this->tpl->load_filter('pre', 'config');
		$this->tpl->assign("BASE_URL", Router::getBaseUrl());
	}

	/**
	 * Gets a smarty instance (singleton)
	 *
	 * @return Smarty
	 */
	public static function getSmartyInstance()
	{
		if (self::$smartyInstance == null)
		{
			ClassLoader::import('library.smarty.libs.Smarty');
			self::$smartyInstance = new Smarty();
			self::$smartyInstance->compile_dir = self::$compileDir;
			self::$smartyInstance->template_dir = ClassLoader::getRealPath("application.view");
		}

		return self::$smartyInstance;
	}

	/**
	 * Sets smarty compile dir
	 *
	 * @param unknown_type $dirPath
	 */
	public static function setCompileDir($dirPath)
	{
		self::$compileDir = $dirPath;
	}

	public function setValue($name, $value)
	{
		$this->tpl->assign($name, $value);
	}

	public function appendValue($name, $value)
	{
		$this->tpl->append($name, $value);
	}

	public function setValueArray($array)
	{
		$this->tpl->assign($array);
	}

	public function setObject($name, $object)
	{
		$this->tpl->assign_by_ref($name, $object);
	}

	public function unsetValue($name)
	{
		$this->tpl->clear_assign($name);
	}

	public function unsetAll()
	{
		$this->tpl->clear_all_assign();
	}

	public function render($view)
	{
		if ($this->tpl->template_exists($view))
		{
			return $this->tpl->fetch($view);
		}
		else
		{
			throw new ViewNotFoundException($view);
		}
	}

	/**
	 * Registers Smarty object
	 *
	 * @param string $title Object title
	 * @param Object $object
	 * @param array $allowed Allowed methods
	 * @param array $blockMethods Array of block method titles
	 */
	public function registerObject($title, $object, $allowed = array(), $blockMethods = array())
	{
		$this->tpl->register_object($title, $object, $allowed, true, $blockMethods);
	}

	/**
	 * Registers application specific helpers
	 *
	 * Helper code file in helper dir should follow following naming convention:
	 * name.type.php
	 * i.e.: formfield.function.php
	 *
	 * Type can be one of these:
	 * - block
	 * - function
	 * - modifier
	 *
	 */
	public function registerHelperList()
	{
		//$path = ClassLoader::getRealPath("application.helper");
		
		$frameworkPluginDir = ClassLoader::getRealPath("framework.helper");
		$frameworkFormPluginDir = ClassLoader::getRealPath("framework.helper.form");
		$applicationPluginDir = ClassLoader::getRealPath("application.helper");
		//$this->tpl->plugins_dir[] = $path;
		$this->tpl->plugins_dir[] = array($frameworkPluginDir, 
										  $frameworkFormPluginDir, 
										  $applicationPluginDir);
	}

	/**
	 * Smarty helper function for creating hyperlinks in application.
	 * As the format of application part addresing migth vary, links should be created
	 * by using this helper method. When the addressing schema changes, all lionks
	 * will be regenerated
	 *
	 * @param array $params List of parameters passed to a function
	 * @param Smarty $smarty Smarty instance
	 * @return string Smarty function resut (formatted link)
	 */
	public function helperFunctionLinkTo($params, Smarty $smarty)
	{
		$result = $this->router->createURL($params);
		return $result;
	}
}

?>