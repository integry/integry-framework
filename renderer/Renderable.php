<?php

ClassLoader::import("framework.renderer.Renderable");

/**
 * Renderable object interface
 *
 * @package	framework.renderer
 * @author Integry Systems
 */
interface Renderable
{
	/**
	 * Renders self into renderer using view if needed
	 *
	 * @param Renderer $renderer Renderer to render into
	 * @param string $view Path to view
	 * @return string Rendered output
	 * @throws ViewNotFoundException if view does not exists
	 */
	public function render(Renderer $renderer, $view);
}

?>