<?php

// run only this unit test case
require_once('Initialize.php');

require_once('ClassLoader.php');

class TestClassLoader extends UnitTest 
{
  	function __construct() 
	{    
		ClassLoader::mountPath('cd',getcwd().'/');    
	}
  
	function testSetBaseDir() 
	{
		$current = ClassLoader::getBaseDir();
		
		// test invalid base dir
		$baseDir = getcwd().'asdasd';
		
		try 
		{
			ClassLoader::setBaseDir($baseDir);		
		}
		catch(Exception $exc)
		{
			$baseDir = '';
		}
		
		$this->assertEqual($baseDir, ClassLoader::getBaseDir());
	  
	  	// test valid base dir
		$baseDir = getcwd();
		
		try 
		{
			ClassLoader::setBaseDir($baseDir);		
		}
		catch(Exception $exc)
		{
			$baseDir = 'it shouldnt raise an exception with a valid directory';
		}
		
		$this->assertEqual($baseDir, ClassLoader::getBaseDir());	  
	}
  
  	function testImportPath() 
	{	    
	    // test valid import path
		$cd = getcwd();	  	
		ClassLoader::importPath($cd,true);  
		$iniPath = explode(';',ini_get('include_path'));
		$this->assertTrue(FALSE !== array_search($cd,$iniPath));		
		
		// [FAILS] test removal from import path
		ClassLoader::remove($cd);  
		$iniPath = explode(';',ini_get('include_path'));
		$this->assertTrue(FALSE === array_search($cd,$iniPath));		

	    // test INVALID import path
		$cd = getcwd().'sdsdsd';	  	
		ClassLoader::importPath($cd,true);  
		$iniPath = explode(';',ini_get('include_path'));
		$this->assertTrue(FALSE === array_search($cd,$iniPath));		
	}
  
  	function testMount() 
	{
	    // register a new VALID mount point
		$cd = getcwd();
		
		try 
		{
			$res = ClassLoader::mountPath('test', $cd);
		}
		catch(Exception $exc)
		{			
		}

		$this->assertEqual(ClassLoader::getRealPath('test'), $cd);		
	    
	    // [FAILS] test unmount - ClassLoader should add trailing slash to base dir path by default
	    ClassLoader::unmountPath('test');
	    $this->assertEqual(ClassLoader::getRealPath('test'),$cd.DIRECTORY_SEPARATOR.'test');
	    
		// [FAILS] mounting a relative path
	    ClassLoader::mountPath('relativetest','..');
	    $this->assertEqual(ClassLoader::getRealPath('relativetest'),realpath('..'));	    
	    
	    // register an INVALID mount point
		$cd = getcwd().'sdsds';
		
		try 
		{
			$res = ClassLoader::mountPath('test', $cd);
			$fail = true;
		}
		catch(Exception $exc)
		{			
			$fail = false;
		}

		$this->assertFalse($fail);		
	}
  
  	function testLoad() 
	{	    
	    ClassLoader::load('LoadClass');
	    $this->assertTrue(class_exists('LoadClass', false));	    
	}
  
  	function testImport() 
  	{
	    ClassLoader::import('cd.ClassLoaderImport.*');	    
	    $imp = new FirstImport();
	    $this->assertTrue(is_object($imp));
	}
  
}

?>