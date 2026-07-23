<?php

namespace RPC\Validator;

use RPC\Validator;

class Image extends Validator
{
	
	public function validate( mixed $filename ): bool
	{
		return (bool) getimagesize( $filename );
	}
	
}

?>
