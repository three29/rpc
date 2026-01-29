<?php

namespace RPC\Bootstraps;

use RPC\Contracts\Bootstrap;

class Errors implements Bootstrap {
	public static function handle()
	{
		register_shutdown_function( array( static::class, 'rpc_shutdown' ) );
		error_reporting( E_ALL );
		ini_set( 'display_errors', 0 );

		if( env( "SHOW_ERRORS" ) === true )
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
		$error = error_get_last();

		// Check if this was a fatal error
		if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ) ) ) {
			// Set 500 status code
			if ( ! headers_sent() ) {
				header( 'HTTP/1.0 500 Internal Server Error' );
			}

			// Only show custom error page in production (when SHOW_ERRORS is not true)
			if ( env( 'SHOW_ERRORS' ) !== true ) {
				// Try to render custom error template
				if ( defined( 'APP_PATH' ) && defined( 'CACHE_PATH' ) ) {
					try {
						$view = new \RPC\View( APP_PATH . '/View', new \RPC\View\Cache( CACHE_PATH . '/view' ) );

						// Try specific error template first, then fallback templates
						if ( file_exists( APP_PATH . '/View/errors/500.php' ) ) {
							$view->display( 'errors/500.php' );
						} elseif ( file_exists( APP_PATH . '/View/errors/5xx.php' ) ) {
							$view->display( 'errors/5xx.php' );
						} else {
							// Generic fallback if no templates exist
							echo '500 - Internal Server Error';
						}
						return;
					} catch ( \Exception $e ) {
						// If view rendering fails, fall through to generic message
					}
				}

				// Fallback generic message
				echo 'Something went wrong. Our amazing team of developers have been notified. Please try again later.';
			}
		}
	}
}