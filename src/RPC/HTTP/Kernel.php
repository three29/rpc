<?php

namespace RPC\HTTP;

use RPC\Application;
use RPC\Contracts\Bootstrap;
use RPC\Exception\Handler;
use RPC\Router;

class Kernel {

	/**
	 * Bytes of response output held back before it is flushed to the client
	 */
	const OUTPUT_CHUNK_SIZE = 1048576;

	/** @var Router */
	protected $router;

	/** @var Application */
	protected $app;

	protected $bootstraps = [
		\RPC\Bootstraps\Environment::class, // Keep this first
		\RPC\Bootstraps\Errors::class, // Before anything that can fail
		\RPC\Bootstraps\Database::class,
		\RPC\Bootstraps\Session::class,
	];

	/**
	 * Create a new HTTP kernel instance.
	 *
	 * @param Router $router
	 */
	public function __construct(Application $app, Router $router)
	{
		$this->router = $router;
		$this->app = $app;
		$this->bootstrap();
		$this->loadRoutes();
	}

	private function bootstrap(): void
	{
		/** @var Bootstrap $class */
		foreach ($this->bootstraps as $class) {
			// Call the handle function on each class
			$class::handle();
		}
	}

	private function loadRoutes(): void
	{
		$routes = [];

		//if this is not cli call initiate routes and session
		if( strpos( php_sapi_name(), 'cli' ) === false )
		{
			$root_path = \RPC\Registry::get('root_path');

			if (!empty($root_path)) {
				$routes = require $root_path . '/config/routes.php';
			}

			\RPC\Registry::set( 'routes', $routes ?: [] );
		}

		$this->router->setRewriteRules( $routes );
	}

	/**
	 * Handle a request
	 *
	 * @return void
	 */
	public function handle()
	{
		// Buffer the response so an error part-way through rendering can replace
		// the partial page with a proper error response. Output past the chunk
		// size is flushed as it is produced, so large downloads are not held in memory.
		$level = ob_get_level();
		Handler::$output_buffer_level = $level;
		ob_start( null, self::OUTPUT_CHUNK_SIZE );

		try {
			$this->router->run();
		} finally {
			while ( ob_get_level() > $level ) {
				ob_end_flush();
			}
			Handler::$output_buffer_level = null;
		}
	}
}