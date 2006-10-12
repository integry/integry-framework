<?php

// run only this unit test case
require_once('Initialize.php');

require_once('Application.php');

/**
* Test cases for Application class
*
* @author Rinalds Uzkalns <rinalds@integry.net>
* @package framework.test
* @todo Test renderer
*/
class TestApplication extends UnitTest 
{
	
	private $app;
	
	/**
	* Initialize application object
	*/
	function setUp() 
	{
		$this->app = Application::getInstance();
	}	  
	
	/**
	* Restore changed properties
	*/
	function tearDown() {
	  
	  
	}
	
	/**
	* Test if application instance is available
	*/
	function testInstance() 
	{	
		$this->assertEqual(get_class($this->app),'Application');	  
	}

	/**
	* Test setting and getting default controller anem
	*/
	function testDefaultController() 
	{	
		$defc = 'test';
		$this->app->setDefaultControllerName($defc);
		$this->assertEqual($defc, $this->app->getDefaultControllerName());
	}
	
	/**
	* Test getting a request object instance
	*/
	function testGetRequest() 
	{	
		$this->assertEqual(get_class($this->app->getRequest()), 'Request');
	}

	/**
	* Test getting a view template path
	*/
	function testGetView() 
	{	
		$this->assertEqual($this->app->getView('testController','testAction'), ClassLoader::getBaseDir().'\application\view\testController\testAction.tpl');
	}
	
	/**
	* Test getting a view layout path
	*/
	function testGetLayout() 
	{	
		$this->assertEqual($this->app->getLayoutPath('testLayout'), ClassLoader::getBaseDir().'\application\view\layout\testLayout.tpl');
	}
	
	/**
	* Test getting a renderer object instance
	*/
	function testGetRenderer() 
	{	
		require_once('mock/MockRenderer.php');
		$this->app->setRenderer(new MockRenderer());
		$this->assertTrue($this->app->getRenderer() instanceof Renderer);
	}
	
	/**
	* Should (?) throw an exception if controller/action doesn't exit
	*/
	function testRender() 
	{
		// initialize renderer
		require_once('mock/MockRenderer.php');
		$this->app->setRenderer(new MockRenderer());
		
		// set up response
		$resp = new ActionResponse;
		
		//invalid controller/action
		try 
		{
			$this->app->render('XtestController', 'XtestAction', $resp); 
			$pass = false;	
		}
		catch (ViewNotFoundException $ex)
		{		  
			$pass = true;		  
		}
		
		$this->assertTrue($pass);
		
	}

}

?>