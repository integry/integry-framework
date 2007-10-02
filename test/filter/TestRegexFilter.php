<?php

require_once (dirname(__FILE__) . '/../Initialize.php');

require_once (FW_DIR . 'request/validator/filter/RegexFilter.php');

/**
 * @package	framework.test
 * @author	Integry Systems 
 */
class TestRegexFilter extends UnitTest
{
	public function testFilter()
	{
		$filter = new RegexFilter('/[^0-9]/');
		$this->assertEqual('123', $filter->apply('z1z2z3'));
	}
}

?>