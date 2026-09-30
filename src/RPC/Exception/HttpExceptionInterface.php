<?php

namespace RPC\Exception;

/**
 * Implemented by exceptions that map to a specific HTTP response status.
 * The exception handler uses it to pick the status code and extra headers;
 * anything that does not implement it is treated as a 500.
 *
 * @package Exception
 */
interface HttpExceptionInterface extends \Throwable
{
	/**
	 * HTTP status code to respond with (400-599)
	 */
	public function getStatusCode(): int;

	/**
	 * Extra response headers, keyed by header name
	 *
	 * @return array<string, string>
	 */
	public function getHeaders(): array;
}
