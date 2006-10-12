<?php

// run only this unit test case
require_once('../Initialize.php');

ClassLoader::import("framework.response.RawResponse");

class TestRawResponse extends UnitTest 
{
  	function testContent() 
	{
		//set content
		$resp = new RawResponse('content');
		$this->assertEqual($resp->getContent(), 'content');
		
		//append content
		$resp->setContent('-test',true);
		$this->assertEqual($resp->getContent(), 'content-test');
	} 
}

?>