<?php

namespace RPC\Validator;

use RPC\Exception\InvalidArgumentException;
use RPC\Validator;


class Between extends Validator
{
	
	protected $min;
	
	protected $max;
	
	function __construct( int|float $min, int|float $max, string $errormessage = '' )
	{
		$this->min = $min;
		$this->max = $max;

		parent::__construct( $errormessage );
	}

	/**
	 * Returns true if it is greater than or equal to $min and less
	 * than or equal to $max, false otherwise.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( mixed $value ): bool
	{
		if( ! is_numeric( $value ) )
		{
			return false;
		}

		return ( $this->min <= $value ) &&
		       ( $this->max >= $value );
	}
	
}

?>
