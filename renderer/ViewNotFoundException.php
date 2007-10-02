<?php

ClassLoader::import('framework.renderer.RendererException');

/**
 * Thrown when view file for a requested controller's action does not exist.
 *
 * @package	framework.renderer
 * @author Integry Systems
 */
class ViewNotFoundException extends RendererException
{
	/**
	 * Template path (view name)
	 */
	private $viewName = "";

	/**
	 * @param string $view Path of not found view
	 */
	public function __construct($view)
	{
		parent::__construct("Specified view ($view) was not found");
		$this->viewName = $view;
	}

	/**
	 * Gets view path
	 *
	 * @return string View path
	 */
	public function getView()
	{
		return $this->viewName;
	}
}

?>
