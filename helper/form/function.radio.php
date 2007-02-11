<?php

/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 * @author Integry Systems <http://integry.com>
 */
function smarty_function_radio($params, $smarty) 
{
	$formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];
	if (!($formHandler instanceof Form))
	{
		throw new HelperException('Element must be placed in {form} block');
	}
	$fieldName = $params['name'];
		
	// get checked state
	$formValue = $formHandler->getValue($fieldName);
	if ((!empty($formValue) && ($formValue == $params['value'])) || (empty($formValue) && $params['checked']))
	{
		$params['checked'] = 'checked';
	}

	$output = '<input type="radio"';
	foreach ($params as $name => $value)
	{
		$output .= ' ' . $name . '="' . $value . '"';
	}
	
	$output .= "/>";
	
	return $output;
}

?>