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
function smarty_function_filefield($params, $smarty) 
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
        if(!AccessStringParser::run($formParams['role']))
        {
            $params['disabled'] = 'disabled'; 
        }
	    unset($params['role']);
	}
	
	$content = '<input type="file"';
	foreach ($params as $name => $param) 
	{
		$content .= ' ' . $name . '="' . $param . '"'; 
	}

	$content .= '/>';
	
	return $content;
}

?>