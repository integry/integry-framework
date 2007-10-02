<?php

/**
 * @package	framework.test
 * @author	Integry Systems 
 */

define('FW_DIR', dirname(dirname(__FILE__)) . '/');

$dir = dirname(__FILE__) . '/_library/';

require_once($dir . 'simpletest/unit_tester.php');
require_once($dir . 'simpletest/reporter.php');
require_once($dir . 'unittest/UnitTest.php');
require_once($dir . 'unittest/UTGroupTest.php');
require_once($dir . 'unittest/UTStandalone.php');

?>