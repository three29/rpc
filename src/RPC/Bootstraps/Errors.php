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
			if( php_sapi_name() !== 'cli' ) {
				$whoops->pushHandler(static::prettyPageHandler());
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

	/**
	 * Whoops page with credentials masked. The page dumps $_ENV, $_SERVER,
	 * $_POST and $_COOKIE, which hold DB_PASSWORD, API keys, submitted
	 * passwords and the session id.
	 */
	protected static function prettyPageHandler(): \Whoops\Handler\PrettyPageHandler
	{
		$handler = new \Whoops\Handler\PrettyPageHandler;
		$pattern = '/pass|secret|key|token|auth|credential|dsn|salt|private/i';

		foreach ( array( '_ENV' => $_ENV, '_SERVER' => $_SERVER, '_POST' => $_POST ) as $global => $values ) {
			foreach ( array_keys( $values ) as $key ) {
				if ( is_string( $key ) && preg_match( $pattern, $key ) ) {
					$handler->hideSuperglobalKey( $global, $key );
				}
			}
		}

		foreach ( array_keys( $_COOKIE ) as $key ) {
			$handler->hideSuperglobalKey( '_COOKIE', $key );
		}

		return $handler;
	}

	public static function handleUncaught( \Throwable $e ): void
	{
		$handler = static::exceptionHandler();
		$handler->report( $e );

		if ( php_sapi_name() !== 'cli' ) {
			$handler->render( $e );
		}
	}

	public static function rpc_shutdown() {
		$error = error_get_last();

		// Check if this was a fatal error
		if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ) ) ) {
			// PHP has already written the fatal error to its log; only render here.
			// With SHOW_ERRORS on, Whoops handles fatals itself.
			if ( env( 'SHOW_ERRORS' ) === true || php_sapi_name() === 'cli' ) {
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
