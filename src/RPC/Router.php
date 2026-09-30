<?php

namespace RPC;

use RPC\Contracts\ExceptionHandler;
use RPC\Exception\Handler;
use RPC\Exception\HttpExceptionInterface;
use RPC\Exception\MethodNotAllowedException;
use RPC\Exception\RouteNotFoundException;
use RPC\Exception\RoutingException;
use RPC\HTTP\Request;
use RPC\HTTP\Response;
use RPC\Regex;

class Router {
	/**
	 * Request methods that can be dispatched to an action, e.g. editGET, editDELETE.
	 * HEAD requests are dispatched to the GET handler.
	 */
	const DISPATCHABLE_METHODS = array( 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' );

	protected array $rewrite_rules = array();

	protected ?ExceptionHandler $exception_handler = null;

	protected string $controller;
	protected string $action;

	protected Request $request;
	protected Response $response;

	protected ?array $params = null;


	public function __construct() {
		$this->controller = 'Home';
		$this->action     = 'index';

		$this->request  = Request::getInstance();
		$this->response = Response::getInstance();

		$this->request->setRouter( $this );
	}

	public function setRewriteRules( array $rules ): void {
		$this->rewrite_rules = array_replace( $this->rewrite_rules, $rules );
	}

	public function setExceptionHandler( ExceptionHandler $handler ): void {
		$this->exception_handler = $handler;
	}

	/**
	 * The handler bound in the application container, or the default one
	 */
	public function getExceptionHandler(): ExceptionHandler {
		if ( $this->exception_handler ) {
			return $this->exception_handler;
		}

		$handler = Application::$app ? Application::$app->make( ExceptionHandler::class ) : null;

		return $this->exception_handler = ( $handler instanceof ExceptionHandler ) ? $handler : new Handler();
	}

	public function run(): void {
		try {
			$this->executeRoute();
		} catch ( \Throwable $e ) {
			$this->handleException( $e );
		}
	}

	/**
	 * Report and render an exception thrown while handling the request.
	 * With SHOW_ERRORS enabled, server errors are re-thrown so Whoops can show them.
	 */
	protected function handleException( \Throwable $e ): void {
		$is_client_error = $e instanceof HttpExceptionInterface && $e->getStatusCode() < 500;

		if ( env( 'SHOW_ERRORS' ) === true && ! $is_client_error ) {
			throw $e;
		}

		$handler = $this->getExceptionHandler();
		$handler->report( $e );
		$handler->render( $e );
	}

	protected function executeRoute(): void {
		$uri = strtolower( trim( $this->request->getURI(), '/' ) );

		/**
		 * If the requested URI does not have a path info, then the default
		 * command and action will be returned
		 */
		if ( $uri && $this->rewrite_rules ) {
			/**
			 * If the string has some GET parameters, they will be ignored during
			 * the routing process
			 */
			if ( ( $pos = strpos( $uri, '?' ) ) !== false ) {
				$uri = substr( $uri, 0, $pos );
			}

			foreach ( $this->rewrite_rules as $rule => $arr ) {
				$matches = array();

				$regex = new \RPC\Regex( '#' . str_replace( '#', '\#', $rule ) . '#' );

				if ( $regex->match( $uri, $matches ) ) {
					$l = count( $matches[0] );

					if ( $l == 1 ) {
						$uri = $arr;
					} else {
						for ( $replace = array(), $search = array(), $i = 1, $l = count( $matches[0] ); $i < $l; $i ++ ) {
							$replace[] = $matches[0][ $i ][0];
							$search[]  = '$' . $i;
						}

						$uri = str_replace( $search, $replace, $arr );
					}

					break;
				}
			}

		}

		if ( $uri ) {
			if ( strpos( $uri, '/params' ) !== false ) {
				list( $uri, $params ) = explode( '/params', $uri );

				$params = explode( '/', substr( $params, 1 ) );
				for ( $i = 0, $l = count( $params ); $i < $l; $i += 2 ) {
					$this->params[ $params[ $i ] ] = @$params[ $i + 1 ];
				}
			}

			$uri = trim( $uri, '/' );

			if ( $uri ) {
				$cmdparts = explode( '/', $uri );

				$cmdkey = end( $cmdparts );
				reset( $cmdparts );
				array_pop( $cmdparts );

				if ( count( $cmdparts ) ) {
					foreach ( $cmdparts as $k => $v ) {
						$cmdparts[ $k ] = ucfirst( $v );
					}
					$this->controller = implode( '\\', $cmdparts );
					$this->action     = $cmdkey;
				} else {
					$cmdparts         = array( ucfirst( $cmdkey ) );
					$this->controller = implode( '\\', $cmdparts );
				}
			}
		}

		$command = 'APP\\Controller\\' . $this->controller;

		if ( ! class_exists( $command ) ) {
			if ( $this->action != 'index' ) {
				$command      .= '\\' . ucfirst( $this->action );
				$this->action = 'index';
			}
		}

		if ( ! class_exists( $command ) ) {
			throw new RouteNotFoundException( 'No controller found for "' . $this->request->getURI() . '"' );
		}

		$command = new $command;
		if ( ! $command instanceof \RPC\Controller ) {
			throw new RoutingException( 'Class "' . get_class( $command ) . '" has to inherit from \RPC\Command' );
		}

		$request = strtoupper( $_SERVER['REQUEST_METHOD'] ?? 'GET' );

		// HEAD is answered by the GET handler; the SAPI drops the body
		$dispatch_method = $request === 'HEAD' ? 'GET' : $request;

		$methodname = $this->action . $dispatch_method;

		if ( ! in_array( $dispatch_method, self::DISPATCHABLE_METHODS, true ) || ! is_callable( array( $command, $methodname ), false ) ) {
			$allowed = array();
			foreach ( self::DISPATCHABLE_METHODS as $method ) {
				if ( is_callable( array( $command, $this->action . $method ), false ) ) {
					$allowed[] = $method;
					if ( $method === 'GET' ) {
						$allowed[] = 'HEAD';
					}
				}
			}

			if ( ! $allowed ) {
				throw new RouteNotFoundException( 'Class "' . get_class( $command ) . '" has no action "' . $this->action . '"' );
			}

			throw new MethodNotAllowedException( $allowed, 'Class "' . get_class( $command ) . '" was found but method "' . $methodname . '" could not be executed' );
		}

		/*
		$command->setParams( $this->getRouter()->getParams() );

		/*
			Methods are called depending on the request type - the type
			name is appended to the method name:
				editGET
				editPOST

			Also, if a method having "setup" appended to its name exists and/or
			one having "teardown" appended, it will be executed before,
			respectively after, either GET or POST methods:
				editSetup
				editTeardown
		*/


		/*
			Validate CSRF
			- default it will validate POST method
			- if called like validateCSRF( 'get' ) it will validate the CSRF from get

			Check .env for DISABLE_CSRF. ignore_csrf can be used in a more granular
			way to enable CSRF for a particular command when its disabled globally
			in .env

			Disabled CSRF:
			DISBABLE_CSRF === true || ignore_csrf === true
			DISABLE_CSRF === true || ignore_csrf undefined or false

			Enabled CSRF:
			DISABLE_CSRF undefined or false && ignore_csrf is undefined or false

		*/
		if ( empty( $command->ignore_csrf ) && ! env( 'DISABLE_CSRF' ) ) {
			$this->request->validateCSRF();
		}

		$command->request  = $this->request;
		$command->response = $this->response;

		$command->current_method     = $this->action;
		$command_name                = get_class( $command );
		$command_name                = explode( '\\', $command_name );
		$command->current_controller = strtolower( end( $command_name ) );

		if ( is_callable( array( $command, 'setup' ), false ) ) {
			$command->setup( $this->request, $this->response );
		}

		if ( is_callable( array( $command, $this->action . 'Setup' ), false ) ) {
			$command->{$this->action . 'Setup'}( $this->request, $this->response );
		}

		$command->$methodname( $this->request, $this->response );

		if ( is_callable( array( $command, $this->action . 'Teardown' ), false ) ) {
			$command->{$this->action . 'Teardown'}( $this->request, $this->response );
		}

		$command->flash = $command->flash();

		if ( ! $command->template ) {
			$command->getView( true )->display();
		}

		if ( is_callable( array( $command, 'teardown' ), false ) ) {
			$command->teardown( $this->request, $this->response );
		}

	}

	public function getParams() {
		return $this->params;
	}
}

?>
