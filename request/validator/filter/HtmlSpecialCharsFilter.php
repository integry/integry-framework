<?php

/**
 * Converts special HTML characters to appropriate display representations
 *
 * @package framework.request.validator.filter
 * @author Rinalds Uzkalns <rinalds@integry.net>
 */
class HtmlSpecialCharsFilter extends RequestFilter
{
	public function apply($value)
	{
		return htmlspecialchars($value);
	}
}

?>