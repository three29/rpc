<?php

namespace RPC\Validator;

use RPC\Validator;

class GT extends Validator
{

	/**
	 * @var int|float $min
	 */
	protected int|float $min;

	/**
	 * @param int|float $min
	 * @param string $errormessage
	 */
	function __construct( int|float $min, string $errormessage = '' )
	{
		parent::__construct( $errormessage );
		$this->min = $min;
	}
	
	/**
	* Returns true if it is greater than $min, false otherwise.
	*
	* @param int|float $value
	* @return bool
	*/
	public function validate( mixed $value ): bool
	{
		return $value > $this->min;
	}
	
}

?>
