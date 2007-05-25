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
	
	private $isAjax = false;
	
	/**
	 * @param array $default Associative array with default values
	 */
	public function __construct()
	{
		$this->setValueArray($_GET);
		$this->setValueArray($_POST);
		
		foreach($_FILES as $varName => $value) 
        { 
            $_FILES[$varName]['uploaded_file_array'] = true; 
        }
		
        $this->setValueArray($_FILES);
		$this->setValueArray($_COOKIE);

		$this->dataContainer = $this->removeMagicQuotes($this->dataContainer);
		
		// Will only work with prototype
		$this->isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']);
	}
	
	public function isAjax()
	{
	    return $this->isAjax;
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
	public function & toArray()
	{
		return $this->dataContainer;
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
