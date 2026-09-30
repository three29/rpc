<?php

namespace RPC\Exception;

/**
 * Thrown when the action exists but has no handler for the request method.
 * Responds with 405 Method Not Allowed and an Allow header.
 *
 * @package Exception
 */
class MethodNotAllowedException extends RoutingException implements HttpExceptionInterface
{
	/**
	 * @var string[]
	 */
	protected array $allowed_methods;

	/**
	 * @param string[] $allowed_methods
	 */
	public function __construct( array $allowed_methods, string $message = '', int $code = 0, ?\Throwable $previous = null )
	{
		$this->allowed_methods = array_values( array_unique( array_map( 'strtoupper', $allowed_methods ) ) );

		parent::__construct( $message, $code, $previous );
	}

	/**
	 * @return string[]
	 */
	public function getAllowedMethods(): array
	{
		return $this->allowed_methods;
	}

	public function getStatusCode(): int
	{
		return 405;
	}

	public function getHeaders(): array
	{
		return array( 'Allow' => implode( ', ', $this->allowed_methods ) );
	}
}
