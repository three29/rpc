<?php

namespace RPC\Exception;

/**
 * Thrown when no controller or action matches the requested URI.
 * Responds with 404 Not Found.
 *
 * @package Exception
 */
class RouteNotFoundException extends RoutingException implements HttpExceptionInterface
{
	public function getStatusCode(): int
	{
		return 404;
	}

	public function getHeaders(): array
	{
		return array();
	}
}
