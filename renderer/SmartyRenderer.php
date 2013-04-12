<?php

ClassLoader::import('framework.renderer.Renderer');
ClassLoader::import('library.smarty.libs.Smarty');

/**
 * Renderer implementation based in Smarty template engine (Smarty wrapper)
 *
 * @package	framework.renderer
 * @author Integry Systems
 */
class SmartyRenderer extends Renderer
{
	/**
	 * Path of a directory where compiled smarty templates are stored
	 *
	 * @var string
	 */
	protected static $compileDir = "";

	protected $helperDirectories = array();

	/**
	 * Template engine instance
	 *
	 * @var Smarty
	 */
	protected $tpl;

	/**
	 * Application instance
	 *
	 * @var Smarty
	 */
	private $application;

	/**
	 * Template renderer constructor
	 *
	 * Creates a smarty instance and sets a compile directory path (this is required
	 * by smarty)
	 */
	public function __construct(Application $application)
	{
		$this->application = $application;

		$this->tpl = $this->getSmartyInstance();
		$this->helperDirectories = array_merge($this->helperDirectories, $this->tpl->getPluginsDir());

		$this->registerHelperList();
		$this->tpl->loadFilter('pre', 'config');
		$this->tpl->assign("BASE_URL", $this->application->getRouter()->getBaseUrl());
	}

	/**
	 * Gets application instance
	 *
	 * @return Smarty
	 */
	public function getApplication()
	{
		return $this->application;
	}

	/**
	 * Gets a smarty instance
	 *
	 * @return Smarty
	 */
	public function getSmartyInstance()
	{
		if (!$this->tpl)
		{
			$this->tpl = new Smarty();
			$this->tpl->compile_dir = self::$compileDir;
			$this->tpl->template_dir = ClassLoader::getRealPath("application.view");
		}

		return $this->tpl;
	}

	/**
	 * Sets smarty compile dir
	 *
	 * @param unknown_type $dirPath
	 */
	public static function setCompileDir($dirPath)
	{
		if (!is_dir($dirPath))
		{
			if (@mkdir($dirPath, 0777, true))
			{
				chmod($dirPath, 0777);
			}
		}

		self::$compileDir = $dirPath;
	}

	public function set($name, $value)
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
		$this->tpl->assign($name, $object);
	}

	public function unsetValue($name)
	{
		$this->tpl->clearAssign($name);
	}

	public function unsetAll()
	{
		$this->tpl->clearAllAssign();
	}

	public function render($view)
	{
		if ($this->tpl->templateExists($view))
		{
			$ignoreStatus = ClassLoader::getIgnoreStatus();
			ClassLoader::ignoreMissingClasses(true);
			$res = $this->tpl->fetch($view);
			ClassLoader::ignoreMissingClasses($ignoreStatus);
			return $res;
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
		$this->tpl->registerObject($title, $object, $allowed, true, $blockMethods);
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
		$this->tpl->setPluginsDir($this->helperDirectories);
	}

	public function registerHelperDirectory($directory)
	{
		$this->helperDirectories[] = $directory;
	}

	public function getViewExtension()
	{
		return 'tpl';
	}
}

?>