<?php

/**
 *  Abstract class for defining condition under which a particular validation have to be performed
 *
 *  This is useful for creating more complex forms where validation rules on the same field may
 *  differ depending on a value that is entered in another field.
 *
 *  Conditional checks are only available at server side (for now)
 *
 *  @package framework.request.validator.check
 *  @author Integry Systems
 */
abstract class CheckCondition
{
	protected $request;
	
	function __construct(Request $request)
	{
		$this->request = $request;			  
	}
	
	abstract function isSatisfied();
}

?>