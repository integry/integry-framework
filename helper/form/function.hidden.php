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
function smarty_function_hidden($params, $smarty)
{
	$formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];
	if (!($formHandler instanceof Form))
	{
		throw new HelperException('Element must be placed in {form} block');
	}
	$fieldName = $params['name'];

	$output = '<input type="hidden"';
	foreach ($params as $name => $value)
	{
		$output .= ' ' . $name . '="' . $value . '"';
	}

	$formValue = $formHandler->getValue($fieldName);
	$output .= ' value="' . $formValue . '"';
	$output .= "/>";

	return $output;
}

?>