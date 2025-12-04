<?php

namespace RPC\Validator;

use RPC\Validator;

class Date extends Validator
{
	
	/**
	 * Date format
	 *
	 * @var string
	 */
	protected string $format = 'Y-m-d';

	/**
	 * @param string $format PHP Date Format String (optional)
	 * @param string $errormessage Error message when date does not match (optional)
	 */
	public function __construct( string $format = 'Y-m-d', string $errormessage = '' )
	{
		parent::__construct( $errormessage );
		$this->format = $format;
	}
	
	/**
	 * Returns true if it is a valid date, false otherwise.
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( mixed $value ): bool
	{
		return \RPC\Date::validDate( $value, $this->format );
	}
	
}

?>
