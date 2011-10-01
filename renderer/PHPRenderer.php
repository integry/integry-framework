<?php

ClassLoader::import('framework.renderer.Renderer');

/**
 * Renderer implementation based on raw PHP files ("PHP itself is a templating engine")
 *
 * @package	framework.renderer
 * @author Integry Systems
 */
class PHPRenderer extends Renderer
{
	protected $values = array();

	public function __construct(Application $application)
	{
		$this->application = $application;
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

	public function set($name, $value)
	{
		$this->values[$name] = $value;
	}

	public function appendValue($name, $value)
	{
		$this->values[$name] = $value;
	}

	public function setValueArray($array)
	{
		$this->values[$name] = $array;
	}

	public function setObject($name, $object)
	{
		$this->values[$name] = $object;
	}

	public function unsetValue($name)
	{
		unset($this->values[$name]);
	}

	public function unsetAll()
	{
		$this->values = array();
	}

	public function render($view)
	{
		if (file_exists($view))
		{
			ob_start();
			extract($this->values);
			include $view;
			$contents = ob_get_contents();
			ob_end_clean();
			return $contents;
		}
		else
		{
			throw new ViewNotFoundException($view);
		}
	}

	public function getViewExtension()
	{
		return 'php';
	}
}

?>