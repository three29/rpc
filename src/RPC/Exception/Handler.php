<?php

namespace RPC\Exception;

use RPC\Contracts\ExceptionHandler;
use Throwable;

/**
 * Default exception handler
 *
 * report() writes server errors (5xx) to the PHP error log, forwards them to
 * Sentry when the SDK is installed, and to any callbacks registered with
 * reportUsing(). Client errors (4xx) are not reported.
 *
 * render() discards any partial output buffered by the kernel, sends the
 * status code and headers, and responds with JSON for JSON/AJAX requests or
 * with the first existing template of errors/{code}.php, errors/{4xx|5xx}.php,
 * falling back to plain text. Templates receive $status_code and $status_text.
 *
 * @package Exception
 */
class Handler implements ExceptionHandler
{
	/**
	 * ob_get_level() before the kernel started buffering the response. Output
	 * buffers above this level belong to the response and are discarded before
	 * an error is rendered. NULL means no response buffering is active.
	 */
	public static ?int $output_buffer_level = null;

	/**
	 * @var callable[]
	 */
	protected static array $reporters = array();

	protected static array $status_texts = array(
		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Page Not Found',
		405 => 'Method Not Allowed',
		410 => 'Gone',
		419 => 'Page Expired',
		422 => 'Unprocessable Content',
		429 => 'Too Many Requests',
		500 => 'Internal Server Error',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
	);

	protected ?string $view_dir;

	protected ?string $cache_dir;

	public function __construct( ?string $view_dir = null, ?string $cache_dir = null )
	{
		$this->view_dir  = $view_dir ?? ( defined( 'APP_PATH' ) ? APP_PATH . '/View' : null );
		$this->cache_dir = $cache_dir ?? ( defined( 'CACHE_PATH' ) ? CACHE_PATH . '/view' : null );
	}

	/**
	 * Register an extra reporter, called with the Throwable for every reported error
	 */
	public static function reportUsing( callable $reporter ): void
	{
		static::$reporters[] = $reporter;
	}

	public static function flushReporters(): void
	{
		static::$reporters = array();
	}

	public function shouldReport( Throwable $e ): bool
	{
		return $this->getStatusCode( $e ) >= 500;
	}

	public function report( Throwable $e ): void
	{
		if ( ! $this->shouldReport( $e ) ) {
			return;
		}

		try {
			$this->log( $this->formatForLog( $e ) );
		} catch ( Throwable $ignored ) {
		}

		if ( function_exists( '\Sentry\captureException' ) ) {
			try {
				\Sentry\captureException( $e );
			} catch ( Throwable $ignored ) {
			}
		}

		foreach ( static::$reporters as $reporter ) {
			try {
				$reporter( $e );
			} catch ( Throwable $ignored ) {
			}
		}
	}

	public function render( Throwable $e ): void
	{
		$response = $this->prepareResponse( $e );

		$this->discardOutputBuffers();

		if ( ! headers_sent() ) {
			http_response_code( $response['status'] );
			foreach ( $response['headers'] as $name => $value ) {
				header( $name . ': ' . $value );
			}
		}

		echo $response['body'];
	}

	/**
	 * Build the error response without sending it
	 *
	 * @return array{status: int, headers: array<string, string>, body: string}
	 */
	public function prepareResponse( Throwable $e ): array
	{
		$status  = $this->getStatusCode( $e );
		$text    = $this->getStatusText( $status );
		$headers = $e instanceof HttpExceptionInterface ? $e->getHeaders() : array();

		if ( $this->wantsJson() ) {
			$headers['Content-Type'] = 'application/json';

			return array(
				'status'  => $status,
				'headers' => $headers,
				'body'    => json_encode( array( 'error' => 1, 'error_message' => $text, 'data' => array() ) ),
			);
		}

		return array(
			'status'  => $status,
			'headers' => $headers,
			'body'    => $this->renderTemplate( $status, $text ) ?? $status . ' - ' . $text,
		);
	}

	public function getStatusCode( Throwable $e ): int
	{
		if ( $e instanceof HttpExceptionInterface ) {
			$status = $e->getStatusCode();

			if ( $status >= 400 && $status <= 599 ) {
				return $status;
			}
		}

		return 500;
	}

	public function getStatusText( int $status ): string
	{
		return static::$status_texts[ $status ] ?? ( $status >= 500 ? 'Server Error' : 'Client Error' );
	}

	/**
	 * One-line summary plus a stack trace that omits call arguments, so
	 * credentials passed to functions such as PDO::__construct() never reach the log
	 */
	public function formatForLog( Throwable $e ): string
	{
		$method = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
		$uri    = $_SERVER['REQUEST_URI'] ?? ( $_SERVER['SCRIPT_NAME'] ?? '' );

		$message = sprintf(
			'[RPC] %s: %s in %s:%d [%s %s]',
			get_class( $e ),
			$e->getMessage(),
			$e->getFile(),
			$e->getLine(),
			$method,
			$uri
		);

		$message .= PHP_EOL . 'Stack trace:' . PHP_EOL . $this->formatTrace( $e );

		for ( $previous = $e->getPrevious(); $previous; $previous = $previous->getPrevious() ) {
			$message .= PHP_EOL . sprintf(
				'Caused by %s: %s in %s:%d',
				get_class( $previous ),
				$previous->getMessage(),
				$previous->getFile(),
				$previous->getLine()
			);
		}

		return $message;
	}

	protected function formatTrace( Throwable $e ): string
	{
		$lines = array();

		foreach ( $e->getTrace() as $i => $frame ) {
			$location = isset( $frame['file'] ) ? $frame['file'] . '(' . ( $frame['line'] ?? 0 ) . ')' : '[internal function]';
			$function = ( $frame['class'] ?? '' ) . ( $frame['type'] ?? '' ) . $frame['function'];
			$lines[]  = '#' . $i . ' ' . $location . ': ' . $function . '()';
		}

		$lines[] = '#' . count( $lines ) . ' {main}';

		return implode( PHP_EOL, $lines );
	}

	protected function log( string $message ): void
	{
		error_log( $message );
	}

	protected function wantsJson(): bool
	{
		$accept       = $_SERVER['HTTP_ACCEPT'] ?? '';
		$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
		$requested    = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

		return str_contains( $accept, 'application/json' )
			|| str_contains( $content_type, 'application/json' )
			|| strcasecmp( $requested, 'XMLHttpRequest' ) === 0;
	}

	/**
	 * Render the most specific error template that exists, or NULL if none does
	 * (or rendering it fails)
	 */
	protected function renderTemplate( int $status, string $text ): ?string
	{
		if ( ! $this->view_dir || ! $this->cache_dir ) {
			return null;
		}

		foreach ( array( $status . '.php', substr( (string) $status, 0, 1 ) . 'xx.php' ) as $template ) {
			if ( ! is_file( $this->view_dir . '/errors/' . $template ) ) {
				continue;
			}

			$level = ob_get_level();

			try {
				$view = new \RPC\View( $this->view_dir, new \RPC\View\Cache( $this->cache_dir ) );
				$view->setVars( array( 'status_code' => $status, 'status_text' => $text ) );

				return $view->render( 'errors/' . $template );
			} catch ( Throwable $ignored ) {
				// A broken error template must not mask the original error
				while ( ob_get_level() > $level ) {
					ob_end_clean();
				}

				return null;
			}
		}

		return null;
	}

	protected function discardOutputBuffers(): void
	{
		if ( static::$output_buffer_level === null ) {
			return;
		}

		while ( ob_get_level() > static::$output_buffer_level ) {
			ob_end_clean();
		}
	}
}
