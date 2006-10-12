<?php

require_once('Initialize.php');
require_once('UTGroupTest.php');

$groupTest = new UTGroupTest();

$groupTest->setName('Framework Test');

$groupTest->addDir(dirname(__FILE__));
$res = $groupTest->run();

?>