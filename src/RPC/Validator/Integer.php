<?php

namespace RPC\Validator;

use RPC\Validator;

class Integer extends Validator
{
	
	/**
	 * Returns true if the given value is an integer
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( mixed $value ): bool
	{
		return (int)$value === $value;
	}
	
}
