<?php

namespace RPC\Bootstraps;

use RPC\Contracts\Bootstrap;

class Session implements Bootstrap {
	public static function handle() {
		// Skip session setup in testing environment to avoid header issues
		if (getenv('APP_ENV') === 'testing') {
			return;
		}

		$session = new \RPC\Session();
		$session->setExpire( 0 );
		$session->setPath( '/' );
		$session->start();
	}
}