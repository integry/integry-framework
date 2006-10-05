<?php

class Route {
	
	/**
	 * Default URL variable regxep patern
	 *
	 * @var string
	 */
	private $defaultVariablePattern =  "[.a-zA-Z0-9]+";
	
	/**
	 * Route regexp that will be applied during the mapping process
	 *
	 * @var string
	 */
	private $regexpPattern = "";
	
	/**
	 * Route URL pattern
	 *
	 * @var string
	 */
	private $URLPattern = "";
	
	/**
	 * Request variables gathered by parsing URL pattern
	 *
	 * @var array
	 */
	private $requestVariableList = array();
	
	private $tokenList = array();
	
	/**
	 * Route contructor
	 *
	 * @param string $URLPattern
	 * @param array $defaultValueList
	 * @param array $varValueRequirements
	 */
	public function __construct($URLPattern, $defaultValueList = array(), $varValueRequirements = array()) {
		$this->URLPattern = $URLPattern;
		
		foreach ($defaultValueList as $name => $value) {
			$this->registerVariable($name, $value);
		}
		
		$URLParts = explode("/", $URLPattern);
		foreach ($URLParts as $var) {
			$varValueRegexp = $this->defaultVariablePattern;
			
			if (substr($var, 0, 1) == ":") {
				$varName = substr($var, 1, strlen($var));
				$varDefaultValue = null;
				if (!empty($defaultValueList[$varName])) {
					$varDefaultValue = $defaultValueList[$varName];
				}
				$this->registerVariable($varName, $varDefaultValue);
				$this->registerToken($varName);
				
				if (!empty($varValueRequirements[$varName])) {
					$varValueRegexp = $varValueRequirements[$varName];
				}
			} else {
				$this->registerToken(null);
				$varValueRegexp = $var;
			}
			if (!empty($this->regexpPattern)) {
				$this->regexpPattern .= "\/" . $varValueRegexp;
			} else {
				$this->regexpPattern .= $varValueRegexp;
			}
		}
		$this->regexpPattern = "/^" . $this->regexpPattern . "/";
	}
	
	public function registerToken($tokenName) {
		$this->tokenList[] = $tokenName;
	}
	
	public function hasTokens() {
		if (!empty($this->tokenList)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getTokenList() {
		return $this->tokenList;
	}
	
	public function registerVariable($varName, $defaultValue = null) {
		$this->requestVariableList[$varName] = $defaultValue;
	}
	
	public function getVariableValue($varName) {
		if (!empty($this->requestVariableList[$varName])) {
			return $this->requestVariableList[$varName];
		} else {
			return null;
		}
	}
	
	public function getVariableList() {
		return $this->requestVariableList;
	}
	
	public function getRegexpPattern() {
		return $this->regexpPattern;
	}
	
	public function getURLPattern() {
		return $this->URLPattern;
	}
	
	public function getAction() {
		return $this->getVariableValue("action");
	}
	
	public function getController() {
		return $this->getVariableValue("controller");
	}
}

?>