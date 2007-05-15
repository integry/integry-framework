<?php
class RolesClassParser
{
    const ROLE_TAG = "@role";
    
    private $roles = array();
    
    private $classRole = '';
    
    public function __construct(ReflectionClass $class)
    {
        $this->parseClass($class);
    }
    
    public function getRoles()
    {
        return $this->roles;
    }
    
    private function parseClass(ReflectionClass $class)
    {        
        $className = $class->getName();
        $this->roles[$className] = $this->parsePHPDoc($class->getDocComment());
        
        if(empty($this->roles[$className]))
        {
            throw new ApplicationException('Controller class shuld always have a role assigned');
        }
        
        foreach($class->getMethods() as $method)
        {
            if($method->isPublic())
            {
                $this->roles[$className . '::' . $method->getName()] = $this->parseMethod($method, $this->roles[$className]);
            }
        }
    }
    
	private function parseMethod(ReflectionMethod $method, $prefix = false)
	{
	    return $this->parsePHPDoc($method->getDocComment(), $prefix);
	}
	
	private function parsePHPDoc($phpDoc, $prefix = false)
	{
		preg_match('/\s*\*\s*'.self::ROLE_TAG.'\s+(\w+)/', $phpDoc, $roleMatches);
		
		$role = $prefix;
		if(!empty($roleMatches))
		{
		    if($prefix)
		    {
		        $role .= '.';
		    }
		    $role .= $roleMatches[1];	    
		}
		
		return $role;
	}
}
?>