<?php

/**
 * Session wrapper class
 *
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
	public function setValue($name, $value)
	{
		if (is_object($value))
		{
			$serializedValue = serialize($value);
			$_SESSION[$name] = $serializedValue;
		}
		else
		{
			$_SESSION[$name] = $value;
		}
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
	 *
	 * @todo restore serialized object
	 */
	public function getValue($name)
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
	 * Returns a session ID
	 *
	 * @return string
	 */
	public function getID()
	{
		return session_id();
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
}

?>
