<?php

namespace RPC\Exception;

/**
 * HTTP exception for RPC framework
 * Thrown when HTTP operations fail
 *
 * The exception code doubles as the HTTP status code, e.g.
 * `new HttpException( 'Gone', 410 )`. Codes outside 400-599 respond with 500.
 *
 * @package Exception
 */
class HttpException extends RuntimeException implements HttpExceptionInterface
{
	/**
	 * @var array<string, string>
	 */
	protected array $headers = array();

	public function getStatusCode(): int
	{
		$code = (int) $this->getCode();

		return ( $code >= 400 && $code <= 599 ) ? $code : 500;
	}

	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * @param array<string, string> $headers
	 */
	public function setHeaders( array $headers ): static
	{
		$this->headers = $headers;

		return $this;
	}
}
