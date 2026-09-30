<?php

namespace RPC\Validator;

use RPC\Validator;

class Alternation extends Validator
{
	
	/**
	 * Given validators
	 *
	 * @var array<\RPC\Validator>
	 */
	protected array $alternates = array();
	
	/**
	 * Adds the given validators to the object
	 */
	public function __construct()
	{
		if( func_num_args() )
		{
			$this->alternates = func_get_args();
		}
	}
	
	/**
	 * Another method to add validators to the object
	 *
	 * @param \RPC\Validator $validator
	 * @return \RPC\Validator\Alternation
	 */
	public function add( \RPC\Validator $validator )
	{
		$this->alternates[] = $validator;
		return $this;
	}
	
	/**
	 * Validates the given validators and returns true if one of them makes it.
	 * In case they all fail, the first error message encountered will be
	 * returned
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function validate( mixed $value ): bool
	{
		foreach( $this->alternates as $validator )
		{
			if( $validator->validate( $value ) )
			{
				return true;
			}
			else
			{
				if( ! $this->getError() )
				{
					$this->setError( $validator->getError() );
				}
			}
		}

		return false;
	}
	
}

?>
