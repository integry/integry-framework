<?php

/**
 * Strip non-numeric characters from a string
 *
 * @package framework.request.validator.filter
 * @author Rinalds Uzkalns <rinalds@integry.net>
 */
class NumericFilter extends Filter
{
	public function apply($value)
	{
		$value = str_replace(',' , '.', $value);

		$parts = explode('.', $value);
		
		//next remove all characters save 0 though 9
		//in both elements of the array
		$dollars = eregi_replace("[^0-9]", null, $parts[0]);
		if (isset($parts[1]))
		{
			$cents = eregi_replace("[^0-9]", null, $parts[1]);
		}
		else
		{
		  	$cents = null;
		}
		
		//if there was a decimal in the original string, put it back
		if((string)$cents != null)
		{
		   $cents = "." . $cents;
		}
		
		return $dollars . $cents;	  	
	}
}

?>