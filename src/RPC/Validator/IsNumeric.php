<?php

namespace RPC\Validator;

use RPC\Validator;

class IsNumeric extends Validator
{
	
	/**
	 * Returns true if the given value is a numeric value
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( mixed $value ): bool
	{
		return is_numeric( $value );
	}
	
}

?>
