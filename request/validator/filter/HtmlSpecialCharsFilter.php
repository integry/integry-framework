<?php

/**
 * Converts special HTML characters to appropriate display representations
 *
 * @package framework.request.validator.filter
 * @author Integry Systems
 */
class HtmlSpecialCharsFilter extends RequestFilter
{
	public function apply($value)
	{
		return htmlspecialchars($value);
	}
}

?>