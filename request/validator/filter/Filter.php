<?php

/**
 * Abstract request value filter
 *
 * @package framework.request.validator
 * @author Saulius Rupainis <saulius@integry.net>
 */
abstract class Filter
{
	abstract public function apply($value);
}

?>