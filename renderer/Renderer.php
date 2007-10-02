<?php

ClassLoader::import('framework.renderer.Renderable');
ClassLoader::import('framework.response.Response');

/**
 * Class for rendering a response.
 *
 * Response rendering consists of displaying collected (assigned) data.
 * Renderer is kind of template engine wrapper.
 *
 * @package	framework.renderer
 * @author Integry Systems
 */
abstract class Renderer
{
	/**
	 * Registers value to render
	 *
	 * @param string $name Name of value
	 * @param scalar $value Value
	 * @return void
	 */
	abstract public function set($name, $value);

	/**
	 * Appends a template variable by a given value
	 *
	 * @param string $name Renderer engine variable name
	 * @param string $value
	 */
	abstract public function appendValue($name, $value);

	/**
	 * Registers value array to render
	 *
	 * @param array $array Associative value array
	 * @return void
	 */
	abstract public function setValueArray($array);

	/**
	 * Register object to renderer
	 *
	 * @param string $name Name of object
	 * @param object $object Object
	 * @return void
	 */
	abstract public function setObject($name, $object);

	/**
	 * Removes value from renderer
	 *
	 * @param string $name Name of value
	 * @return void
	 */
	abstract public function unsetValue($name);

	/**
	 * Removes all values from renderer
	 *
	 * @return void
	 */
	abstract public function unsetAll();

	/**
	 * Process
	 *
	 * @param Renderable $object Object to render
	 * @param string $view Path to view
	 * @return string Rendered output
	 * @throws ViewNotFoundException if view does not exists
	 */
	public function process(Renderable $object, $view)
	{
		try
		{
			return $object->render($this, $view);
		}
		catch(ViewNotFoundException $ex)
		{
			throw $ex;
		}
	}

	/**
	 * Performs rendering and returns the result
	 *
	 * @param string $view Path to view
	 * @return string Rendered output
	 * @throws ViewNotFoundException if view does not exists
	 */
	abstract public function render($view);
}

?>
