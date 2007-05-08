<?php

/**
 * Smarty form helper
 *
 * <code>
 * </code>
 *
 * @package application.helper
 * @author Saulius Rupainis <saulius@integry.net>
 *
 * @todo Include javascript validator source
 */
function smarty_block_form($params, $content, $smarty, &$repeat)
{
	$handle = $params['handle'];
	unset($params['handle']);
	if (!($handle instanceof Form))
	{
		throw new HelperException('Form must have a Form instance assigned! (handle=$formInstance)');
	}

	$formAction = $params['action'];
	unset($params['action']);
	
    if ('self' != $formAction)
    {
        $vars = explode(" ", $formAction);
    	$URLVars = array();
    
    	foreach ($vars as $var)
    	{
    		$parts = explode("=", $var, 2);
    		$URLVars[$parts[0]] = $parts[1];
    	}
    
    	$router = Router::getInstance();
    
    	try
    	{
    		$actionURL = $router->createURL($URLVars);
    	}
    	catch (RouterException $e)
    	{
    		$actionURL = "INVALID_FORM_ACTION_URL";
    	}
    }
    else
    {
        $actionURL = $_SERVER['REQUEST_URI'];
    }
	
	if (!empty($params['onsubmit']))
	{
		$customOnSubmit = $params['onsubmit'];
		unset($params['onsubmit']);
	}

	$onSubmit = "";
	$validatorField = "";
	$preValidate = "";
	
	if (isset($params['prevalidate']))
	{	  
		$preValidate = $params['prevalidate'] . '; ';
		unset($params['prevalidate']);
	}
	
	if ($handle->isClientSideValidationEnabled())
	{
		if (!empty($customOnSubmit))
		{
			$onSubmit = $preValidate . 'if (!validateForm(this)) { return false; } ' . $customOnSubmit;
		}
		else
		{
			$onSubmit = 'return validateForm(this);';
		}		
		
		require_once("function.includeJs.php");
		smarty_function_includeJs(array("file" => "library/formvalidator.js"), $smarty);

		$validatorField = '<input type="hidden" disabled="disabled" name="_validator" value="' . $handle->getValidator()->getJSValidatorParams() . '"/>';
		$filterField = '<input type="hidden" disabled="disabled" name="_filter" value="' . $handle->getValidator()->getJSFilterParams() . '"/>';
	
        $params['onkeyup'] = 'applyFilters(this, event);';
    }
	else
	{
		$onSubmit = $customOnSubmit;
	}

	if ($onSubmit)
	{
        $params['onsubmit'] = $onSubmit;
    }
    
    $formAttributes ="";
	foreach ($params as $param => $value)
	{
		$formAttributes .= $param . '="' . $value . '" ';
	}

	$form = '<form action="'.$actionURL.'" '.$formAttributes.'>' . "\n";
	$form .= $validatorField;
	$form .= $filterField;
	$form .= $content;
	$form .= "</form>";
	return $form;
}

?>