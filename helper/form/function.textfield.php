<?php

/**
 * Renders text field
 *
 * If you wish to use autocomplete on a text field an additional parameter needs to be passed:
 *	
 * <code>
 *	  autocomplete="controller=somecontroller field=fieldname"
 * </code>
 *
 * The controller needs to implement an autoComplete method, which must return the AutoCompleteResponse 
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 * @author Saulius Rupainis <saulius@integry.net>
 */
function smarty_function_textfield($params, $smarty) 
{
	$formParams = $smarty->_tag_stack[0][1];
	$handle = $formParams['handle'];
	$fieldName = $params['name'];

	if (!isset($params['id']))
	{
	  	$params['id'] = $params['name'];
	}
	
	$content = '<input type="text"';
	foreach ($params as $name => $param) {
		$content .= ' ' . $name . '="' . $param . '"'; 
	}

	$content .= ' value="' . $handle->getValue($fieldName) . '"';
	$content .= '/>';

	if (isset($params['autocomplete']))
	{
	  	$acparams = array();
		foreach (explode(' ', $params['autocomplete']) as $param)
	  	{
			list($p, $v) = explode('=', $param, 2);
			$acparams[$p] = $v;
		}
		 
		$url = Router::getInstance()->createURL(array('controller' => $acparams['controller'], 
													  'action' => 'autoComplete', 
													  'query' => 'field=' . $params['name']));
		  
		$content .= '<div id="autocomplete_' . $params['id'] . '" class="autocomplete"></div>';
		$content .= '<script type="text/javascript">
						new Ajax.Autocompleter("' . $params['id'] . '", "autocomplete_' . $params['id'] . '", "' . $url . '", {frequency: 0.2});
					</script>';
	}
	
	return $content;
}

?>