<?php

namespace RPC\Validator;

use RPC\Validator;

class Alpha extends Validator
{
	
	/**
	 * Validates if every character of $value is a letter
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( mixed $value ): bool
	{
		return ctype_alpha( (string) $value );
	}
	
}

?>
