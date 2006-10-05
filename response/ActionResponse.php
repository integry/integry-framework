<?php

ClassLoader::import('framework.response.Response');
ClassLoader::import('framework.renderer.Renderable');

/**
 * Class for creating a response that containes action specific data (results) which should be rendered in some way (i.e. TemplateRenderer)
 *
 * @package	framework.response
 * @author Saulius Rupainis <saulius@integry.net>
 */
class ActionResponse extends Response implements Renderable {

	/**
	 * Stores values
	 */
	protected $dataContainer = array();

	/**
	 * Stores value array
	 */
	protected $arrayContainer = array();

	/**
	 * Registered object strorage
	 */
	protected $objectContainer = array();
	
	/**
	 * A smarter way to set response data - this method automaticaly recognizes type of a 
	 * given data and chooses a proper method to register it
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function set($name, $value) {
		
		if (is_object($value)) {
			$this->setObject($name, $value);
		} else {
			$this->setValue($name, $value);
		}
	}
	
	/**
	 * Registers value to response
	 *
	 * @param string $name Name of value
	 * @param scalar $value Value
	 * @return void
	 */
	public function setValue($name, $value) {
		$this->dataContainer[$name] = $value;
	}
	
	/**
	 * Gets a response variable name
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getValue($name) {
		return $this->dataContainer[$name];
	}
	
	/**
	 * Appends variable value (string)
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function appendValue($name, $value) {
		
		if (!empty($this->dataContainer[$name])) {
			if (!is_array($value) && !is_array($this->dataContainer[$name])) {
				$this->dataContainer[$name] .= $value;
			} else {
				$arr = array_merge($value, $this->getValue($name));
				$this->setValue($name, $arr);
			}
		} else {
			$this->setValue($name, $value);
		}
	}

	/**
	 * Registers value array to render
	 *
	 * @param array $array Associative value array
	 * @return void
	 */
	public function setValueArray($array) {
		$this->arrayContainer[] = $array;
	}

	/**
	 * Register object to renderer
	 *
	 * @param string $name Name of object
	 * @param object $object Object
	 * @return void
	 */
	public function setObject($name, $object) {
		$this->objectContainer[$name] = $object;
	}

	public function render(Renderer $renderer, $view) {
		/* Set values */
		foreach ($this->dataContainer as $name => $value) {
			$renderer->setValue($name, $value);
		}

		/* Set value arrays */
		foreach ($this->arrayContainer as $array) {
			$renderer->setValueArray($array);
		}

		/* Set objects */
		foreach ($this->objectContainer as $name => $object) {
			$renderer->setObject($name, $object);
		}

		/* Render */
		try {
			return $renderer->render($view);
		} catch (ViewNotFoundException $ex) {
			throw $ex;
		}
	}
	
	public function getData() {
		return;
	}

}

?>