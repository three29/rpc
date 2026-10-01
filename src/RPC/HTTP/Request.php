<?php

namespace RPC\HTTP;

use RPC\Exception\TokenMismatchException;
use RPC\HTTP\Cookie;

/**
 * Represents the request sent by the client
 *
 * @package HTTP
 */
class Request
{

	/**
	 * HEAD request method
	 */
	const METHOD_HEAD = 'head';

	/**
	 * GET request method
	 */
	const METHOD_GET  = 'get';

	/**
	 * POST request method
	 */
	const METHOD_POST = 'post';

	/**
	 * PUT request method
	 */
	const METHOD_PUT  = 'put';

	protected ?string $uri = null;

	/**
	 * All the headers
	 *
	 * @var array|null
	 */
	protected ?array $headers = null;

	/*
	 * All params
	 */

	protected ?array $params = null;

    /*
     * Current router
     */
	protected ?\RPC\Router $router = null;

	/**
	 * Hash containing all the get variables sent with the request
	 *
	 * @var array
	 */
	public array $get = array();

	/**
	 * Hash containing all the post variables sent with the request
	 *
	 * @var array
	 */
	public array $post = array();

	/**
	 * Hash containing all the file variables sent with the request
	 *
	 * @var array
	 */
	public array $files = array();

	/**
	 * Class constructor
	 * Now supports dependency injection while maintaining getInstance() for backward compatibility
	 */
	public function __construct()
	{
		$this->post  = $_POST;
		$this->get   = $_GET;
		$this->files = $_FILES;
	}

	/**
	 * Returns an instance of \RPC\HTTP\Response. Subsequent calls to this method
	 * will return the same object
	 *
	 * @return static
	 */
	public static function getInstance(): static
	{
		if( ! isset( $GLOBALS['_RPC_']['singleton']['request'] ) )
		{
			$c = __CLASS__;
			$GLOBALS['_RPC_']['singleton']['request'] = new $c;
		}

		return $GLOBALS['_RPC_']['singleton']['request'];
	}

	/**
	 * Returns a cookie object
	 *
	 * @param string $name
	 *
	 * @return \RPC\HTTP\Cookie
	 */
	public function getCookie( string $name ): Cookie
	{
		return new \RPC\HTTP\Cookie( $name );
	}

	/**
	 * Returns the context path.
	 *
	 * Returns the portion of the request URI that indicates the
	 * context of the request.
	 *
	 * Example
	 * <code>
	 *  // if the request is done to http://www.example.com/user/list/params/client/3
	 *  $contextPath = $this->getContextPath();
	 *  // $contextPath will be "/user/list"
	 * </code>
	 *
	 * @return string The context path or null if there is none
	 */
	public function getContextPath(): string
	{
		$pathinfo = $this->getPathInfo();

		$pos = strpos( $pathinfo, '/params' );
		if( $pos !== false )
		{
			return substr( $pathinfo, 0, $pos );
		}

		return '/';
	}

	/**
	 * Gets the value of header. Returns the value of the specified request
	 * header or null if not found
	 *
	 * @param  string $name The name of the header
	 *
	 * @return string The value of the specified header
	 */
	public function getHeader( string $name ): ?string
	{
		if( is_null( $this->headers ) )
		{
			$this->headers = $this->getHeaders();
		}

		return $this->headers[$name] ?? null;
	}

	/**
	 * Returns an associative array of all the header names and values of this
	 * request
	 *
	 * @return array
	 */
	public function getHeaders(): array
	{
		if( is_null( $this->headers ) )
		{
			if( function_exists( 'getallheaders' ) )
			{
				$this->headers = getallheaders();
			}
			else
			{
				// PERFORMANCE: Parse headers from $_SERVER more efficiently
				$this->headers = [];

				foreach( $_SERVER as $k => $v )
				{
					// Only process HTTP_ headers
					if( strncmp( $k, 'HTTP_', 5 ) !== 0 )
					{
						continue;
					}

					// Convert HTTP_ACCEPT_LANGUAGE -> Accept-Language
					$header_name = str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $k, 5 ) ) ) ) );
					$this->headers[$header_name] = $v;
				}

				// Add CONTENT_TYPE and CONTENT_LENGTH if present (not prefixed with HTTP_)
				if( isset( $_SERVER['CONTENT_TYPE'] ) )
				{
					$this->headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
				}
				if( isset( $_SERVER['CONTENT_LENGTH'] ) )
				{
					$this->headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
				}
			}
		}

		return $this->headers;
	}

	/**
	 * Sets the router on the request, which will allow the request
	 * to have a callable getParam method
	 *
	 * @param \RPC\Router $router
	 *
	 * @return static
	 */
	public function setRouter( \RPC\Router $router ): static
	{
		$this->router = $router;
		return $this;
	}

	/**
	 * Returns the value of the $param parameter or $default if
	 * it's not set
	 *
	 * @param string $param
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function getParam( ?string $param, mixed $default = null ): mixed
	{
		if( is_null( $this->params ) )
		{
			$this->params = $this->router->getParams();
		}

		return isset( $this->params[$param] ) ? $this->params[$param] : $default;
	}

	/**
	 * Gets the ip address.
	 *
	 * Returns the Internet Protocol (IP) address of the client that
	 * sent the request. X-Forwarded-For, X-Real-IP and Client-IP can be set
	 * by anyone, so they are only used when REMOTE_ADDR is a proxy listed in
	 * the TRUSTED_PROXIES env variable (comma separated IPs or CIDR ranges,
	 * or "*" to trust any proxy). Otherwise REMOTE_ADDR is returned.
	 *
	 * @return string The ip address
	 */
	public function getIP(): ?string
	{
		$remote = $_SERVER['REMOTE_ADDR'] ?? null;
		$remote = is_string( $remote ) && $remote !== '' ? $remote : null;

		if( $remote === null || ! self::isTrustedProxy( $remote ) )
		{
			return $remote;
		}

		if( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && is_string( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
		{
			// Each proxy appends the address it received the request from, so walk
			// the chain from the right and return the first untrusted address
			$chain = array_reverse( array_map( 'trim', explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$ip    = null;

			foreach( $chain as $hop )
			{
				if( filter_var( $hop, FILTER_VALIDATE_IP ) === false )
				{
					break;
				}

				$ip = $hop;

				if( ! self::isTrustedProxy( $hop ) )
				{
					break;
				}
			}

			if( $ip !== null )
			{
				return $ip;
			}
		}

		foreach( array( 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP' ) as $header )
		{
			$ip = $_SERVER[$header] ?? null;
			if( is_string( $ip ) && filter_var( trim( $ip ), FILTER_VALIDATE_IP ) !== false )
			{
				return trim( $ip );
			}
		}

		return $remote;
	}

	/**
	 * Checks if an address belongs to the TRUSTED_PROXIES env variable
	 */
	public static function isTrustedProxy( string $ip ): bool
	{
		$trusted = env( 'TRUSTED_PROXIES' );

		if( ! is_string( $trusted ) || trim( $trusted ) === '' )
		{
			return false;
		}

		foreach( array_map( 'trim', explode( ',', $trusted ) ) as $range )
		{
			if( $range === '*' || self::ipInCidr( $ip, $range ) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if an IPv4/IPv6 address matches an address or CIDR range
	 */
	protected static function ipInCidr( string $ip, string $range ): bool
	{
		[ $subnet, $bits ] = array_pad( explode( '/', $range, 2 ), 2, null );

		$ip_bin     = @inet_pton( $ip );
		$subnet_bin = @inet_pton( $subnet );

		if( $ip_bin === false || $subnet_bin === false || strlen( $ip_bin ) !== strlen( $subnet_bin ) )
		{
			return false;
		}

		$max  = strlen( $ip_bin ) * 8;
		$bits = $bits === null ? $max : (int) $bits;

		if( $bits < 0 || $bits > $max )
		{
			return false;
		}

		$bytes = intdiv( $bits, 8 );
		if( substr( $ip_bin, 0, $bytes ) !== substr( $subnet_bin, 0, $bytes ) )
		{
			return false;
		}

		$remainder = $bits % 8;
		if( $remainder === 0 )
		{
			return true;
		}

		$mask = chr( ( 0xFF << ( 8 - $remainder ) ) & 0xFF );

		return ( $ip_bin[$bytes] & $mask ) === ( $subnet_bin[$bytes] & $mask );
	}

	/**
	 * Returns the name of the HTTP method with which this request
	 * was made: HEAD, POST, GET, PUT - this also maps to the METHOD_* constants
	 * defined on the class
	 *
	 * @return string
	 */
	public function getMethod(): string
	{
		return strtolower( $_SERVER['REQUEST_METHOD'] );
	}

	/**
	 * Returns the requested URI
	 *
	 * @return string
	 */
	public function getURI(): string
	{
		return $_SERVER['REQUEST_URI'];
	}

	/**
	 * Checks to see if the request has been made in a SSL protected medium
	 *
	 * @return bool
	 */
	public function isSecure(): bool
	{
		if( isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) == 'on' )
		{
			return true;
		}

		// TLS terminated by a trusted load balancer / proxy
		$remote = $_SERVER['REMOTE_ADDR'] ?? '';
		if( is_string( $remote ) && $remote !== '' && self::isTrustedProxy( $remote ) )
		{
			return strtolower( (string) ( $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '' ) ) === 'https';
		}

		return false;
	}

	/**
	 * Checks if the request was made asynchronously (i.e. AJAX)
	 *
	 * @return bool
	 */
	public function isXHR(): bool
	{
		return ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] );
	}


	/**
	 * Checks if this is ajax call (alias for isXHR)
	 */
	public function isAjax(): bool
	{
		return $this->isXHR();
	}


	/**
	 * Gets the query string
	 *
	 * Returns the query string this is contained in the request
	 * URL after the path
	 *
	 * @return string The query string
	 */
	public function getQueryString(): string
	{
		// Not every SAPI sets it (php -S, CLI) when there is no query string
		return $_SERVER['QUERY_STRING'] ?? '';
	}

	/**
	 * Returns the path info
	 *
	 * @return string
	 */
	public function getPathInfo(): ?string
	{
		return isset( $_SERVER['PATH_INFO'] ) ? $_SERVER['PATH_INFO'] : null;
	}

	/**
	 * Returns the server's IP address
	 *
	 * @return string
	 */
	public function getServerAddr(): string
	{
		return $_SERVER['SERVER_ADDR'];
	}

	/**
	 * Returns the host name of the server that received the request
	 *
	 * @return string
	 */
	public function getServerName(): string
	{
		return $_SERVER['SERVER_NAME'];
	}

	/**
	 * Returns the port number on which this request was received
	 *
	 * @return string
	 */
	public function getServerPort(): string
	{
		return $_SERVER['SERVER_PORT'];
	}

	/**
	 * Returns the server's hostname
	 *
	 * @return string
	 */
	public function getHostName(): string
	{
		return $_SERVER['HTTP_HOST'];
	}


	/**
	 * Validate CSRF_TOKEN from GET or POST
	 *
	 * @return boolean
	 */
	public function validateCSRF( string $method = 'post' ): bool
	{
		$method = strtolower( $method );

		if( $this->getMethod() == $method )
		{
			$params = $method === self::METHOD_GET ? $this->get : $this->post;
			$token  = $params['csrf_token'] ?? $this->getCsrfHeader();

			$csrf_token_pieces = is_string( $token ) ? explode( '_', $token ) : array();
			if( count( $csrf_token_pieces ) != 2 ||
				! \RPC\Util::validCsrf( $csrf_token_pieces[0], $csrf_token_pieces[1] ) ) {
            	throw new TokenMismatchException( 'CSRF token missing or invalid. Please go back and refresh your page.' );
        	}
		}

		return true;
	}

	/**
	 * CSRF token sent in an X-CSRF-Token header (for AJAX requests)
	 */
	protected function getCsrfHeader(): ?string
	{
		$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

		return is_string( $token ) ? $token : null;
	}


	/**
	 * Parse json input
	 */
	public function json(): ?array
	{
		try
		{
			$data = json_decode( file_get_contents( 'php://input' ), true );

			// A scalar JSON body ("abc", 1) is not an object/array
			return is_array( $data ) || is_null( $data ) ? $data : array();
		}
		catch( \Exception $e )
		{
			return array();
		}
	}


}

?>
