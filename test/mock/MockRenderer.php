<?php

ClassLoader::import('framework.renderer.Renderer');

/**
 * A renderer class without implemented methods used for testing
 */
class MockRenderer extends Renderer 
{
	/**
	 * Registers value to render
	 *
	 * @param string $name Name of value
	 * @param scalar $value Value
	 * @return void
	 */
	public function setValue($name, $value) {}
	
	/**
	 * Appends a template variable by a given value
	 *
	 * @param string $name Renderer engine variable name
	 * @param string $value
	 */
	public function appendValue($name, $value) {}

	/**
	 * Registers value array to render
	 *
	 * @param array $array Associative value array
	 * @return void
	 */
	public function setValueArray($array) {}

	/**
	 * Register object to renderer
	 *
	 * @param string $name Name of object
	 * @param object $object Object
	 * @return void
	 */
	public function setObject($name, $object) {}

	/**
	 * Removes value from renderer
	 *
	 * @param string $name Name of value
	 * @return void
	 */
	public function unsetValue($name) {}

	/**
	 * Removes all values from renderer
	 *
	 * @return void
	 */
	public function unsetAll() {}

	/**
	 * Process
	 *
	 * @param FwRenderable $object Object to render
	 * @param string $view Path to view
	 * @return string Rendered output
	 * @throws FwViewNotFoundException if view does not exists
	 */
	public function process(Renderable $object, $view) {
		try {
			return $object->render($this, $view);
		} catch (ViewNotFoundException $ex) {
			throw $ex;
		}
	}

	/**
	 * Performs rendering and returns the result
	 *
	 * @param string $view Path to view
	 * @return string Rendered output
	 * @throws FwViewNotFoundException if view does not exists
	 */
	public function render($view) {}

}

?>