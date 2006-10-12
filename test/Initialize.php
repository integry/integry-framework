<?php

$cd = getcwd();
chdir(dirname(__FILE__));

error_reporting(E_ALL);

require_once('../ClassLoader.php');

// set unittest and simpletest library root directory
$libDir = dirname(__FILE__) . '\..\../';

ClassLoader::mountPath('simpletest',realpath($libDir . 'simpletest/'));

ClassLoader::mountPath('unittest',realpath($libDir . 'library/unittest') . '/');
ClassLoader::mountPath('testdir',dirname(__FILE__).'/');

ClassLoader::mountPath('framework', dirname(dirname(__file__)).'/');
ClassLoader::import("framework.*");
ClassLoader::import("framework.request.Request");
ClassLoader::import("framework.request.Router");
ClassLoader::import("framework.renderer.TemplateRenderer");
ClassLoader::import("framework.controller.*");
ClassLoader::import("framework.response.*");
ClassLoader::import("application.controller.*");
ClassLoader::import("simpletest.*");
ClassLoader::import("unittest.*");
ClassLoader::import("testdir.*");

ClassLoader::load('unit_tester');
ClassLoader::load('mock_objects');
ClassLoader::load('reporter');

ClassLoader::import('unittest.UnitTest');

require_once('UTStandalone.php');

chdir($cd);

?>