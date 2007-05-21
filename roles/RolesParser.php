<?php
class RolesParser
{
    /**
     * Role PHPDoc parameter name
     *
     */
    const ROLE_TAG = "@role";
    
    /**
     * Array of parsed roles
     * 
     * @var array
     */
    private $roles = array();
    
    /**
     * Names of all roles used in this class
     * 
     * @var array
     */
    private $roleNames = array();
    
    /**
     * Assigned class name
     *
     * @var string
     */
    private $className;
    
    /**
     * Path to file with cached class roles
     *
     * @var string
     */
    private $cacheFile;
    
    /**
     * Path to class file with parsed roles
     *
     * @var string
     */
    private $parsedFile;
    
    /**
     * Shows if roles where expired during this script run
     * 
     * @var boolean
     */
    private $wereExpired = false;
    
    /**
     * Create roles object
     *
     * @param string $parsedFile Path to parsed file
     * @param string $cacheFile Path to cache file
     */
    public function __construct($parsedFile, $cacheFile)
    {
        $this->parsedFile = $parsedFile;
        $this->cacheFile = $cacheFile;
        $this->className = substr(basename($parsedFile), 0, -4);
        
        
        @include_once $this->parsedFile;
        
        if($this->isExpired())
        {
            $this->parseClass();
            $this->cache();
        }
        else
        {
            include_once $this->cacheFile;
            $this->roles = $roles;
        }
        
        // Make role names list
        foreach($this->roles as $roleName)
        {
            $this->addRoleName($roleName);
        }
    }
    
    /**
     * Returns true if parsed roles are expired
     *
     * @return boolean
     */
    public function isExpired()
    {
        $expired = !file_exists($this->cacheFile) || filemtime($this->parsedFile) > filemtime($this->cacheFile);
        $this->wereExpired = $expired;
        
        return $expired;
    }
    
    /**
     * Returns true if roles where expired during this script run
     *
     * @return boolean
     */
    public function wereExpired()
    {
        return $this->wereExpired;
    }
    
    /**
     * Get array of curent class roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }
    
    /**
     * Get names of all roles used in this class
     *
     * @return array
     */
    public function getRolesNames()
    {
        return $this->roleNames;
    }
    
    /**
     * Get parsed class name
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }
     
    /**
     * Cache roles object into file
     */
    public function cache()
    {
        @file_put_contents($this->cacheFile, "<?php\n" . $this->toPHPString() . "\n?>");
    }
       
    /**
     * Get role by specifying method name
     *
     * @param stirng $method Required method name
     * @return string
     */
    public function getRole($method)
    {
        return $this->roles["{$this->className}::$method"];
    }
    
    /**
     * Convert roles array into php code which could later be written into file
     *
     * @return string
     */
    private function toPHPString()
    {
        $phpString = "";
        foreach($this->getRoles() as $method => $role)
        {
            $phpString .= "\$roles['$method'] = '$role';\n";
        }
        return $phpString;
    }

    /**
     * Parse class and create roles array
     *
     */
    private function parseClass()
    {        
        $reflectionClass = new ReflectionClass($this->className);
        $className = $reflectionClass->getName();
        $this->roles[$className] = $this->parsePHPDoc($reflectionClass->getDocComment());

        foreach($reflectionClass->getMethods() as $method)
        {
            if($method->isPublic() && !$method->isConstructor())
            {
                $this->roles[$className . '::' . $method->getName()] = $this->parseMethod($method, $this->roles[$className]);
            }
        }
    }
    
    private function addRoleName($roleName)
    {
        if(!in_array($roleName, $this->roleNames)) $this->roleNames[] = $roleName;
    }
    
    /**
     * Parse class method to create roles array entry
     */
	private function parseMethod(ReflectionMethod $method, $prefix = false)
	{
	    return $this->parsePHPDoc($method->getDocComment(), $prefix);
	}
	
	/**
	 * Parse PHPDoc block form role parameters
	 *
	 * @param string $phpDoc PHPDoc block string
	 * @param string $prefix Append a prefix to the role if this value is specified. Also if PHPDoc block doesn't contain role parameter in it use prefix instead
	 * @return unknown
	 */
	private function parsePHPDoc($phpDoc, $prefix = '')
	{
		preg_match('/\s*\*\s*'.self::ROLE_TAG.'\s+(\w+)/', $phpDoc, $roleMatches);
		
		$role = $prefix;
		if(!empty($roleMatches))
		{
		    if(!empty($prefix))
		    {
		        $role .= '.';
		    }
		    $role .= $roleMatches[1];	    
		}
		
		return $role;
	}
}
?>