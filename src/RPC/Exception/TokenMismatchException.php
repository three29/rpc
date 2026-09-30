<?php

namespace RPC\Exception;

/**
 * Thrown when a request fails CSRF token validation.
 * Responds with 419 (page expired), matching the common framework convention.
 *
 * @package Exception
 */
class TokenMismatchException extends SecurityException implements HttpExceptionInterface
{
	public function getStatusCode(): int
	{
		return 419;
	}

	public function getHeaders(): array
	{
		return array();
	}
}
