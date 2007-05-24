<?php

/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper
 */
function smarty_function_password($params, $smarty)
{
	$formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];
	if (!($formHandler instanceof Form))
	{
		throw new HelperException('Element must be placed in {form} block');
	}
	$fieldName = $params['name'];

	$output = '<input type="password"';
	
	// Check permissions
	if(isset($formParams['role']))
	{	
        ClassLoader::import('framework.roles.AccessStringParser');
        if(!AccessStringParser::run($formParams['role']))
        {
            $params['disabled'] = 'disabled'; 
        }
	    unset($params['role']);
	}
	
	if (isset($params['value']))
	{
		unset($params['value']);
	}
	
	foreach ($params as $name => $value)
	{
		$output .= ' ' . $name . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
	}

	$output .= " />";

	return $output;
}

?>