<?php

namespace RPC\Validator;

use RPC\Exception\InvalidArgumentException;
use RPC\Validator;

/**
 * Matches the given string against a regex
 * 
 * @package Validate
 */
class Regex extends Validator
{
	
	/**
	 * Pattern against which the value will be matched
	 * 
	 * @var string
	 */
	protected $pattern;
	
	/**
	 * Sets the regex and error message in case the string doesn't match it
	 *
	 * @param string $pattern
	 * @param string $errormessage
	 */
	function __construct( string $pattern, string $errormessage = '' )
	{
		if( empty( $pattern ) )
		{
			throw new InvalidArgumentException( 'You must supply a valid pattern' );
		}

		$this->pattern = $pattern;
		parent::__construct( $errormessage );
	}

	/**
	 * Matches the given string against the stored regex
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function validate( mixed $value ): bool
	{
		if( is_int( $value ) )
		{
			$value = '' . $value;
		}

		if( ! is_string( $value ) )
		{
			return false;
		}

		return (bool) preg_match( $this->pattern, $value );
	}
	
}

?>
