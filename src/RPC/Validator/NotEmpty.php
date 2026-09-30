<?php

namespace RPC\Validator;

use RPC\Validator;

class NotEmpty extends Validator
{
	
	public function validate( mixed $value ): bool
	{
		return ! empty( $value );
	}
	
}

?>
