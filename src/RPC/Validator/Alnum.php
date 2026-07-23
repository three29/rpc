<?php

namespace RPC\Validator;

use RPC\Validator;

class Alnum extends Validator
{
	
	/**
	 * Validates if every characther is either a letter or a number
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( mixed $value ): bool
	{
		return ctype_alnum( $value );
	}
	
}

?>
