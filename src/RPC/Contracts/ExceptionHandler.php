<?php

namespace RPC\Contracts;

use Throwable;

/**
 * Handles exceptions that escape a request.
 *
 * Bind an implementation in the application container under this interface
 * to replace the default \RPC\Exception\Handler.
 */
interface ExceptionHandler
{
	/**
	 * Record the exception (logs, error trackers). Must not throw.
	 */
	public function report( Throwable $e ): void;

	/**
	 * Send an error response for the exception.
	 */
	public function render( Throwable $e ): void;
}
