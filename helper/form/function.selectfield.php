<?php

/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 * @author Integry Systems
 */
function smarty_function_selectfield($params, $smarty) 
{
	$formParams = $smarty->_tag_stack[0][1];
	$handle = $formParams['handle'];
	
	$options = $params['options'];
	if (empty($options))
	{
		$options = array();
	}
	unset($params['options']);

	$defaultValue = $params['value'];
	unset($params['value']);
	
	if (!isset($params['id']))
	{
	  	$params['id'] = $params['name'];
	}
	
	// Check permissions
	if(isset($formParams['role']))
	{
        ClassLoader::import('framework.roles.AccessStringParser');
        if(!AccessStringParser::run($params['role']))
        {
            $params['disabled'] = 'disabled'; 
        }
	    unset($params['role']);
	}
	
	
	$content = '<select';
	foreach ($params as $name => $param) {
		$content .= ' ' . $name . '="' . $param . '"'; 
	}
	$content .= ">\n";
	$fieldValue = $handle->getValue($params['name']);
	if (is_null($fieldValue))
	{
        $fieldValue = $defaultValue;
	}
	
	foreach ($options as $value => $title)
	{
		if ($fieldValue == $value && (strlen($fieldValue) == strlen($value)))
		{
			$content .= "\t" . '<option value="' . $value . '" selected="selected">' . htmlspecialchars($title)  . '</option>' . "\n";
		}
		else
		{
			$content .= "\t" . '<option value="' . $value . '">' . htmlspecialchars($title) . '</option>' . "\n";
		}
	}
	$content .= "</select>";
	
	return $content;
}

?>