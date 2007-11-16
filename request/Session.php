<?php

/**
 * Session class. Since it is basically a wrapper class for the global $_SESSION array
 * it is implemented as a Monostate - there can be multiple Session instances sharing
 * the same data.
 *
 * @author Integry Systems
 * @package framework.request
 */
class Session
{
	public function __construct($name = null)
	{
		if (!empty($name))
		{
			session_name($name);
		}
		
		@session_start();
	}

	/**
	 * Registers a session variable
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function set($name, $value)
	{
		if (is_object($value))
		{
			$value = serialize($value);
		}

		$_SESSION[$name] = $value;
	}

	/**
	 * Unsets a session variable
	 *
	 * @param string $name
	 */
	public function unsetValue($name)
	{
		unset($_SESSION[$name]);
	}

	/**
	 * Gets a session variable value
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get($name)
	{
		if (!empty($_SESSION[$name]))
		{
			return $_SESSION[$name];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Returns a session variable value and immediately removes it from session
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function pullValue($name)
	{
		$value = $this->get($name);
		$this->unsetValue($name);
		
		return $value;
	}

	public function getObject($name)
	{
		if (!empty($_SESSION[$name]))
		{			
			return unserialize($_SESSION[$name]);
		}
		else
		{
			return null;
		}		
	}

	public function isValueSet($value)
	{
		return isset($_SESSION[$value]);
	}

	/**
	 * Returns a session ID
	 *
	 * @return string
	 */
	public function getID()
	{
		return session_id();
	}

	/**
	 *	Return controller-specific session data (data saved to session by particular controller)
	 */
	public function getControllerData(Controller $controller, $key = '')
	{
		$hash = $this->getControllerHash($controller);	
		if (isset($_SESSION['controller'][$hash]))
		{
			if ($key)
			{
				if (isset($_SESSION['controller'][$hash][$key]))
				{
					return $_SESSION['controller'][$hash][$key];
				}	
				else
				{
					return array();
				}
			}
			else
			{
				return $_SESSION['controller'][$hash];	
			}			
		}
		else
		{
			return array();
		}
	}

	/**
	 *	Set controller-specific session data
	 */
	public function setControllerData(Controller $controller, $key, $value)
	{
		$hash = $this->getControllerHash($controller);	
		$_SESSION['controller'][$hash][$key] = $value;
	}
	
	/**
	 * Destroys this session
	 */
	public function destroy()
	{
		unset($_SESSION);
		session_destroy();
		unset($this);
	}
	
	private function getControllerHash(Controller $controller)
	{
		$hash = array();
		$hash[] = get_class($controller);
		while ($controller)
		{
			$controller = get_parent_class($controller);
			$hash[] = $controller;
		}
		
		return md5(implode(',', $hash));		
	}

}

?>