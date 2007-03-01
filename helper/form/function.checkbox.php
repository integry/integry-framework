<?php

/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 * @author Saulius Rupainis <saulius@integry.net>
 */
function smarty_function_checkbox($params, $smarty) 
{
	if(!isset($params['value'])) $params['value'] = 1;
    
    $formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];
	if (!($formHandler instanceof Form))
	{
		throw new HelperException('Element must be placed in {form} block');
	}
	$fieldName = $params['name'];
	
	if (!isset($params['id']))
	{
	  	$params['id'] = $params['name'];
	}
	
	$output = '<input type="checkbox"';
	foreach ($params as $name => $value)
	{
		$output .= ' ' . $name . '="' . $value . '"';
	}
	
	$formValue = $formHandler->getValue($fieldName);
	if ($formValue == $params['value'] || ('on' == $params['value'] && 1 == $formValue))
	{
		$output .= ' checked="checked"';
	}
	$output .= "/>";
	
	return $output;
}

?>