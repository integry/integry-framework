<?php

// run only this unit test case
require_once('../Initialize.php');

ClassLoader::import("framework.response.RedirectResponse");

class TestRedirectResponse extends UnitTest 
{
  	function testRedirect() 
	{
		$resp = new RedirectResponse('/');
		$this->assertEqual($resp->getHeader('Location'), '/');
	} 
}

?>