<?php

namespace RPC\Bootstraps;

use RPC\Contracts\Bootstrap;

class Session implements Bootstrap {
	public static function handle() {
		// Skip session setup in testing environment to avoid header issues
		if (env('APP_ENV') === 'testing') {
			return;
		}

		$session = new \RPC\Session();
		$session->setExpire( 0 );
		$session->setPath( '/' );
		// Keep the session cookie away from JavaScript, and off plain HTTP
		// when the site is served over HTTPS
		$session->setHTTPOnly( true );
		$session->setSecure( \RPC\HTTP\Request::getInstance()->isSecure() || env( 'SESSION_SECURE_COOKIE' ) === true );
		$session->start();
	}
}
