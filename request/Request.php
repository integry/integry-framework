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
 * @author Integry Systems
 */
class Request
{
	/**
	 * Controller variable name in request array
	 */
	const CONTROLLER_NAME = 'controller';

	/**
	 * Action variable name in request array
	 */
	const ACTION_NAME = 'action';

	/**
	 * Request data storage
	 * @var array
	 */
	private $dataContainer = array();

	/**
	 * Unsanitized request data storage, use with care!
	 * @var array
	 */
	private $rawDataContainer = array();

	/**
	 * @param array $default Associative array with default values
	 */
	public function __construct()
	{
		$this->rawDataContainer = $_REQUEST;
		$this->sanitizeArray($_GET);

		$this->setValueArray($_GET);
		$this->setValueArray($_POST);

		foreach($_FILES as $varName => $value)
		{
			$_FILES[$varName]['uploaded_file_array'] = true;
		}

		$this->setValueArray($_FILES);
		$this->setValueArray($_COOKIE);

		$this->dataContainer = $this->removeMagicQuotes($this->dataContainer);
		$this->dataContainer['__server'] = $_SERVER;
		$this->dataContainer['ip'] = $this->getIP();
	}

	/**
	 * Registers value to request
	 *
	 * @param string $name Name of value
	 * @param scalar $value Value
	 * @return void
	 */
	function set($name, $value)
	{
		$this->dataContainer[$name] = $value;
	}

	/**
	 * Unset a value
	 *
	 * @param string $name Name of value
	 * @return void
	 */
	function remove($name)
	{
		unset($this->dataContainer[$name]);
	}

	/**
	 * Register an array of values to a request
	 *
	 * @param array $dataArray Associative array with values
	 * @return void
	 */
	function setValueArray($dataArray)
	{
		if (!is_array($dataArray))
		{
			$dataArray = array();
		}
		$this->dataContainer = array_merge($this->dataContainer, $dataArray);
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
				$varList[$name] = $this->get($name);
			}
		}
		return $varList;
	}

	/**
	 * Gets a variable value from a request
	 *
	 * @param mixed $name Name of variable. String - returns value by simple key. Array - returns value from multi-level array, for example, array('firstlevel', 'key')
	 * @param mixed $default Default value to return
	 * @return mixed
	 */
	public function get($name, $default = null)
	{
		if (is_array($name))
		{
			$data = $this->dataContainer;
			foreach ($name as $key)
			{
				if (isset($data[$key]))
				{
					$data = $data[$key];
				}
				else
				{
					return $default;
				}
			}

			return $data;
		}

		if (isset($this->dataContainer[$name]))
		{
			return $this->dataContainer[$name];
		}

		return $default;
	}

	/**
	 * Gets a unsanitized request parameters
	 *
	 * @return array
	 */
	public function getRawRequest() {
		return $this->rawDataContainer;
	}

	/**
	 * Returns clients IP address
	 */
	public function getIP()
	{
		return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
	}

	/**
	 * Returns clients IP address as integer
	 */
	public function getIPLong()
	{
		return ip2long($this->getIP());
	}

	/**
	 * Gets an action name from a request
	 *
	 * @return mixed null if there is no action name value
	 */
	public function getActionName()
	{
		return $this->get(self::ACTION_NAME);
	}

	/**
	 * Gets a controller name from a request
	 *
	 * @return mixed null if there is no controller name value
	 */
	public function getControllerName()
	{
		return $this->get(self::CONTROLLER_NAME);
	}

	public function setActionName($actionName)
	{
		return $this->set(self::ACTION_NAME, $actionName);
	}

	/**
	 * Gets a controller name from a request
	 *
	 * @return mixed null if there is no controller name value
	 */
	public function setControllerName($controllerName)
	{
		return $this->set(self::CONTROLLER_NAME, $controllerName);
	}

	/**
	 * Checks if such a request variable has an assigned value
	 *
	 * @param string $name Name of value
	 * @return boolean true if there is $name value, false otherwise
	 */
	public function isValueSet($name)
	{
		if(isset($this->dataContainer[$name]) && is_array($this->dataContainer[$name]) && isset($this->dataContainer[$name]['uploaded_file_array'])) {
			return 0 == $this->dataContainer[$name]['error'];
		}

		return isset($this->dataContainer[$name]);
	}

	/**
	 * Returns request values as associative array
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->dataContainer;
	}

	public function clearData()
	{
		$this->dataContainer = array();
	}

	public function sanitizeAllData()
	{
		$this->sanitizeArray($this->dataContainer);
	}

	public function sanitizeArray(&$data)
	{
		foreach ($data as &$value)
		{
			if (is_array($value))
			{
				$this->sanitizeArray($value);
			}
			else
			{
				$value = strip_tags($value);
			}
		}
	}

	private function removeMagicQuotes ($postArray, $trim = false, $isFile = false)
	{
	   if (get_magic_quotes_gpc() == 1)
	   {
			$newArray = array();

			foreach ($postArray as $key => $val)
			{
				if (is_array($val))
				{
					$newArray[$key] = $this->removeMagicQuotes ($val, $trim, isset($val['uploaded_file_array']));
				}
				else
				{
					if ($trim == true)
					{
						$val = trim($val);
					}

					if($isFile && 'tmp_name' == $key) $newArray[$key] = $val;
					else $newArray[$key] = stripslashes($val);
			   }
		   }

		   return $newArray;
	   }
	   else
	   {
		   return $postArray;
	   }
	}
}

?>
