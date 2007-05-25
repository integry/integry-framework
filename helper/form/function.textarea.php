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
function smarty_function_textarea($params, $smarty) 
{
	$formParams = $smarty->_tag_stack[0][1];
	$handle = $formParams['handle'];
	$fieldName = $params['name'];
	
	if (!isset($params['id']))
	{
	  	$params['id'] = $params['name'];
	}
	
	// Check permissions
	if(isset($formParams['role']))
	{
        ClassLoader::import('application.helper.AccessStringParser');
        if(!AccessStringParser::run($params['role']))
        {
            $params['readonly'] = 'readonly'; 
        }
	    unset($params['role']);
	}
	
	$content = '<textarea';
	foreach ($params as $name => $param) {
		$content .= ' ' . $name . '="' . $param . '"'; 
	}
	//$content .= ' validate="' . $handle->getValidator()->getJSValidatorParams($fieldName) . '"'; 
	$content .= '>' . $handle->getValue($fieldName) . '</textarea>';
	
	return $content;
}

?>