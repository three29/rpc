<?php

namespace RPC\Bootstraps;

use RPC\Contracts\Bootstrap;
use RPC\Contracts\ExceptionHandler;
use RPC\Exception\Handler;

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
		else
		{
			// Exceptions thrown outside the router (bootstraps, CLI scripts).
			// Don't stack a second copy if handle() runs more than once.
			$handler  = array( static::class, 'handleUncaught' );
			$previous = set_exception_handler( $handler );
			if ( $previous === $handler ) {
				restore_exception_handler();
			}
		}
	}

	public static function handleUncaught( \Throwable $e ): void
	{
		$handler = static::exceptionHandler();
		$handler->report( $e );

		if ( strpos( php_sapi_name(), 'cli' ) === false ) {
			$handler->render( $e );
		}
	}

	public static function rpc_shutdown() {
		$error = error_get_last();

		// Check if this was a fatal error
		if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ) ) ) {
			// PHP has already written the fatal error to its log; only render here.
			// With SHOW_ERRORS on, Whoops handles fatals itself.
			if ( env( 'SHOW_ERRORS' ) === true || strpos( php_sapi_name(), 'cli' ) !== false ) {
				return;
			}

			static::exceptionHandler()->render(
				new \ErrorException( $error['message'], 0, $error['type'], $error['file'], $error['line'] )
			);
		}
	}

	protected static function exceptionHandler(): ExceptionHandler
	{
		$handler = \RPC\Application::$app ? \RPC\Application::$app->make( ExceptionHandler::class ) : null;

		return $handler instanceof ExceptionHandler ? $handler : new Handler();
	}
}
