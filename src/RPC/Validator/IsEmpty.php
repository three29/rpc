<?php

namespace RPC\Validator;

use RPC\Validator;

class IsEmpty extends Validator
{
	
	/**
	 * Checks if the given value is empty
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( mixed $value ): bool
	{
		return empty( $value );
	}
	
}

?>
