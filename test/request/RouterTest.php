<?php

require_once (dirname(__FILE__) . '/../Initialize.php');

require_once (FW_DIR . 'request/Router.php');

/**
 * @package	framework.test
 * @author	Integry Systems
 */
class RouterTest extends UnitTest
{
	public function setUp()
	{
		parent::setUp();

		$this->request = new Request();
		$this->router = new Router($this->request);
		$this->router->setBaseDir('/', '/');
	}

	public function testCreate_Controller_Action_ID_URL()
	{
		$this->router->connect(":controller/:action/:id", array(), array("id" => "-?[0-9]+"));
		$this->assertEqual('/cat/view/6', $this->router->createUrl(array('controller' => 'cat', 'action' => 'view', 'id' => 6)));
	}

	public function testCreateUrlWithQueryString()
	{
		$this->router->connect(":controller/:id", array('action' => 'index'), array("id" => "-?[0-9]+"));
		$this->assertEqual('/cat/6?act=view', $this->router->createUrl(array('controller' => 'cat', 'id' => 6, 'query' => array('act' => 'view'))));
	}

	public function testCreateUrlWithExtraParameters()
	{
		$this->router->connect(":controller/:handle-:id", array('action' => 'index'), array("id" => "-?[0-9]+"));
		$this->assertEqual('/cat/test-6', $this->router->createUrl(array('controller' => 'cat', 'id' => 6, 'handle' => 'test')));
	}

	public function testCreateUrlWithExtraParametersDuplicatedInQueryString()
	{
		$this->router->connect(":controller/:handle-:id", array('action' => 'index'), array("id" => "-?[0-9]+"));
		$this->assertEqual('/cat/test-6', $this->router->createUrl(array('controller' => 'cat', 'id' => 6, 'handle' => 'test', 'query' => array('handle' => 'WOOT'))));
	}

}

?>