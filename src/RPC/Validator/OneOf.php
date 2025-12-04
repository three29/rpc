<?php

namespace RPC\Validator;

use RPC\Exception\InvalidArgumentException;
use RPC\Validator;


class OneOf extends Validator
{
	
	protected $values;
	
	public function __construct( array|object $values, string $errormessage = '' )
	{
		$this->values = $values;

		parent::__construct( $errormessage );
	}

	/**
	 * Returns true if the given value if within the given array/object
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( mixed $value ): bool
	{
		$valid = false;
		foreach( $this->values as $v )
		{
			if( $value == $v )
			{
				$valid = true;
				break;
			}
		}

		return $valid;
	}
	
}

?>
