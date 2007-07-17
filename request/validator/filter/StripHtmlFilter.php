<?php

/**
 * Strip HTML tags from a string
 *
 * @package framework.request.validator.filter
 * @author Rinalds Uzkalns <rinalds@integry.net>
 */
class StripHtmlFilter extends RequestFilter
{
	public function apply($value)
	{
		return strip_tags($value);
	}
}

?>