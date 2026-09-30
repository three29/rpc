<?php

namespace RPC\Validator;

use RPC\Validator;
use RPC\Regex;

/**
 * Checks if the given string is an URI
 * 
 * @package Validate
 */
class URI extends Validator
{
	
	/**
	 * Checks if the given string is an URI
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function validate( mixed $value ): bool
	{
		return (bool) preg_match( Regex::URI, $value );
	}
	
}

?>
