<?php

namespace RPC\Validator;

use RPC\Regex;
use RPC\Validator;

class Name extends Validator
{
	
	/**
	 * Returns value if it is a valid format for a person's name,
	 * false otherwise.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( mixed $value ): bool
	{
		return (bool) preg_match( Regex::NAME, $value );
	}
	
}
