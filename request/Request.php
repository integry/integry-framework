<?php

/**
 * Class for accessing and manipulating request data
 *
 * Request class acts as central request data storage which also allows to access
 * application-specific data directy - controller and action name
 *
 * @see self::getControllerName()
 * @see self::getActionName()
 *
 * @package	framework.request
 * @author Saulius Rupainis <saulius@integry.net>
 */
class Request
{
	/**
	 * Controller variable name in request array
	 */
	const CONTROLLER_NAME = 'controller';

	/**
	 * Action variable name iun request array
	 */
	const ACTION_NAME = 'action';

	/**
	 * Request data storage
	 * @var array
	 */
	private $dataContainer = array();

	/**
	 * @param array $default Associative array with default values
	 */
	public function __construct()
	{
		$this->setValueArray($_GET);
		$this->setValueArray($_POST);
		$this->setValueArray($_COOKIE);
	}

	/**
	 * Registers value to request
	 *
	 * @param string $name Name of value
	 * @param scalar $value Value
	 * @return void
	 */
	function setValue($name, $value)
	{
		$this->dataContainer[$name] = $value;
	}

	/**
	 * Register an array of values to a request
	 *
	 * @param array $dataArray Associative array with values
	 * @return void
	 */
	function setValueArray($dataArray)
	{
		$this->dataContainer = @array_merge($this->dataContainer, $dataArray);
	}

	/**
	 * Gets a list of request variables
	 *
	 * @param array $varNameList Arrays of request variables name
	 * @return array Associative array where index maps to variable name
	 */
	public function getValueArray($varNameList = array())
	{
		$varList = array();
		foreach($varNameList as $name)
		{
			if ($this->isValueSet($name))
			{
				$varList[$name] = $this->getValue($name);
			}
		}
		return $varList;
	}

	/**
	 * Gets a variable value from a request
	 *
	 * @param string $name Name of variable
	 * @param mixed $default Default value to return
	 * @return mixed
	 */
	function getValue($name, $default = null)
	{
		if (isset($this->dataContainer[$name]))
		{
			return $this->dataContainer[$name];
		}
		return $default;
	}

	/**
	 * Gets an action name from a request
	 *
	 * @return mixed null if there is no action name value
	 */
	public function getActionName()
	{
		return $this->getValue(self::ACTION_NAME);
	}

	/**
	 * Gets a controller name from a request
	 *
	 * @return mixed null if there is no controller name value
	 */
	public function getControllerName()
	{
		return $this->getValue(self::CONTROLLER_NAME);
	}

	/**
	 * Checks if such a request variable has an assigned value
	 *
	 * @param string $name Name of value
	 * @return boolean true if there is $name value, false otherwise
	 */
	public function isValueSet($name)
	{
		if (!empty($this->dataContainer[$name]))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Returns request values as associative array
	 *
	 * @return array
	 */
	public function & toArray()
	{
		return $this->dataContainer;
	}

}

?>