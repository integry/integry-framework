<?php

// run only this unit test case
require_once('../Initialize.php');

ClassLoader::import("framework.response.ActionRedirectResponse");

class TestActionRedirectResponse extends UnitTest 
{
	/**
	* Instance of the response object
	*/
	protected $inst;
		
  	function testConstructor() 
	{
		$this->inst = new ActionRedirectResponse('testController', 'testAction', array('param' => 'value'));
		$this->assertEqual($this->inst->getControllerName(),'testController'); 
		$this->assertEqual($this->inst->getActionName(),'testAction'); 
		$this->assertEqual($this->inst->getParamList(),array('param' => 'value')); 	    
	}

  	function testController() 
	{
		$this->inst->setControllerName('testController');
		$this->assertEqual($this->inst->getControllerName(),'testController'); 
	}

  	function testAction() 
	{
		$this->inst->setActionName('testAction');
		$this->assertEqual($this->inst->getActionName(),'testAction'); 
	}

  	function testParamList() 
	{
		$this->inst->setControllerName(array('param' => 'value'));
		$this->assertEqual($this->inst->getParamList(),array('param' => 'value')); 
	}
  
}

?>