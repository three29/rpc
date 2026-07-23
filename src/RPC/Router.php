<?php

namespace RPC;

use RPC\Exception\RoutingException;
use RPC\HTTP\Request;
use RPC\HTTP\Response;
use RPC\Regex;

class Router {
	protected array $rewrite_rules = array();

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

	public function run(): void {
		try {
			$this->executeRoute();
		} catch ( \Exception $e ) {
			// Only show custom error page in production (when SHOW_ERRORS is not true)
			if ( env( 'SHOW_ERRORS' ) === true ) {
				// In development, re-throw to let Whoops handle it
				throw $e;
			}

			// Production: show custom 500 error page
			if ( ! headers_sent() ) {
				$this->response->setStatus( '500 Internal Server Error' );
			}

			// Try to render custom error template
			try {
				$view = new \RPC\View( APP_PATH . '/View', new \RPC\View\Cache( CACHE_PATH . '/view' ) );

				// Try specific error template first, then fallback templates
				if ( file_exists( APP_PATH . '/View/errors/500.php' ) ) {
					$view->display( 'errors/500.php' );
				} elseif ( file_exists( APP_PATH . '/View/errors/5xx.php' ) ) {
					$view->display( 'errors/5xx.php' );
				} else {
					// Generic fallback if no templates exist
					echo '500 - Internal Server Error';
				}
			} catch ( \Exception $viewException ) {
				// If view rendering fails, show generic message
				echo 'Something went wrong. Our amazing team of developers have been notified. Please try again later.';
			}

			exit;
		}
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
			// Handle 404 - Laravel-style error page lookup
			$this->response->setStatus( '404 Not Found' );

			// Create view instance for error template
			$view = new \RPC\View( APP_PATH . '/View', new \RPC\View\Cache( CACHE_PATH . '/view' ) );

			// Try specific error template first, then fallback templates
			if ( file_exists( APP_PATH . '/View/errors/404.php' ) ) {
				$view->display( 'errors/404.php' );
			} elseif ( file_exists( APP_PATH . '/View/errors/4xx.php' ) ) {
				$view->display( 'errors/4xx.php' );
			} else {
				// Generic fallback if no templates exist
				echo '404 - Page Not Found';
			}

			exit;
		}

		$command = new $command;
		if ( ! $command instanceof \RPC\Controller ) {
			throw new RoutingException( 'Class "' . get_class( $command ) . '" has to inherit from \RPC\Command' );
		}

		if ( ! in_array( $_SERVER['REQUEST_METHOD'], array( 'GET', 'POST', 'PUT' ) ) ) {
			return;
		}

		$request = $_SERVER['REQUEST_METHOD'];


		$methodname = $this->action . $request;

		if ( ! is_callable( array( $command, $methodname ), false ) ) {
			throw new RoutingException( 'Class "' . get_class( $command ) . '" was found but method "' . $methodname . '" could not be executed' );
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
