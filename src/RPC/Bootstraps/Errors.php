<?php

namespace RPC\Bootstraps;

use RPC\Contracts\Bootstrap;

class Errors implements Bootstrap {
	public static function handle()
	{
		register_shutdown_function( array( static::class, 'rpc_shutdown' ) );
		error_reporting( E_ALL );
		ini_set( 'display_errors', 0 );

		if( getenv( "SHOW_ERRORS" ) === "true" )
		{
			ini_set( 'display_errors', 1 );
			$whoops = new \Whoops\Run;
			if( strpos( php_sapi_name(), 'cli' ) === false ) {
				$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
			} else {
				$whoops->pushHandler(new \Whoops\Handler\PlainTextHandler);
			}
			$whoops->register();
		}
	}

	public static function rpc_shutdown() {
		if( count( func_get_args() ) )
		{
			echo 'Something went wrong. Our amazing team of developers have been notified. Please try again later.';
		}
	}
}