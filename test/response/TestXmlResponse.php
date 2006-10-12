<?php

// run only this unit test case
require_once('../Initialize.php');
require_once('mock/MockRenderer.php');

ClassLoader::import("framework.response.XMLResponse");

class TestXmlResponse extends UnitTest 
{
  	function testHeader() 
	{
		$resp = new XmlResponse();		
		$this->assertEqual($resp->getHeader('Content-Type'), 'text/xml');
	} 
	
	function testBinary()
	{
		$this->XmlTestFlatArray(true);  	
	}
	
	function testNonBinary()
	{
		$this->XmlTestFlatArray(false);  	
	}

	function XmlTestFlatArray($binary = false)
	{
		$resp = new XmlResponse();		
		
		for ($k = 1; $k <= 10; $k++) 
		{
			$resp->setValue($this->createParam(10), $this->createSentence(10,$binary));  			  
		}
		
		ob_start();
		$resp->render(new MockRenderer(),'NULL');
		$xmlString = ob_get_contents();
		ob_end_clean();	  		  
		
		try
		{
			$xml = new SimpleXMLElement($xmlString);  
		}
		catch (Exception $exc) 
		{
			$xml = false;
		}	

		$this->assertTrue(is_object($xml));
	}


	/**
	* Test for valid XML array
	*/
	function DoNotYetTestArray()
	{
		$resp = new XmlResponse();		
		$resp->setValue('response', $this->createDataStructure(10,5,false));
		ob_start();
		$resp->render(new MockRenderer(),'NULL');
		$xmlString = ob_get_contents();
		echo $xmlString;exit;
		ob_end_clean();	  		  
		
		try
		{
			$xml = new SimpleXMLElement($xmlString);  
		}
		catch (Exception $exc) 
		{

		}	

		$this->assertTrue(is_object($xml));
	}
	
	private function createDataStructure($maxElements, $maxLevels, $allowBinary = false) 
	{
	  
		$arr = array();
		for ($k = 1; $k <= rand(1, $maxElements); $k++) 
		{
			if ($maxLevels == 1) 
			{
				$arr[$this->createParam(10)] = $this->createSentence(10, $allowBinary);			  
			}
			else 
			{
				$arr[$this->createParam(10)] = $this->createDataStructure($maxElements, $maxLevels - 1, $allowBinary);  
			}			
		}  	  
		
		return $arr;
	}
	
	private function createParam($maxLength)
	{
		$ret = '';
		for ($k = 1; $k <= rand(1,$maxLength); $k++)
		{
		 	$ret .= chr(rand(97,122)); 	
		}  
		
		return $ret;
	}
	
	private function createSentence($maxWords, $allowBinary = false)
	{
		$words = array();
		for ($k = 1; $k <= rand(1,$maxWords); $k++)
		{
		 	$words[] = $this->createWord(15, $allowBinary);
		}  
		
		return implode(' ', $words);
	}

	private function createWord($maxLength, $allowBinary)
	{
		$ret = '';
		for ($k = 1; $k <= rand(1,$maxLength); $k++)
		{
		 	if ($allowBinary) 
		 	{
				$ret .= chr(rand(0,255));   
			}
			else 
			{
				$ret .= chr(rand(97,122));   			  
			}			  	
		}
		
		return $ret;  
	}

}

?>