<?php

ClassLoader::import("framework.request.Router");

/**
 * Smarty helper function for creating hyperlinks in application.
 * As the format of application part addresing migth vary, links should be created
 * by using this helper method. When the addressing schema changes, all lionks
 * will be regenerated
 *
 * @param array $params List of parameters passed to a function
 * @param Smarty $smarty Smarty instance
 * @return string Smarty function resut (formatted link)
 */
function smarty_function_link($params, $smarty)
{
	$router = Router::getInstance();
	$router->createURL($params);
	return $result;
}

?>