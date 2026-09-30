<?php

namespace RPC\Validator;

use RPC\Validator;
use RPC\Regex;

/**
 * Checks if the given string is a valid domain name
 * 
 * @package Validate
 */
class Domain extends Validator
{
	
	/**
	 * Checks if the given string is a valid domain name
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function validate( mixed $value ): bool
	{
		return (bool) preg_match( Regex::DOMAIN, $value );
	}
	
}

?>
