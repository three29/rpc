<?php

namespace RPC\HTTP;

use RPC\Application;
use RPC\Contracts\Bootstrap;
use RPC\Router;

class Kernel {

	/** @var Router */
	protected $router;

	/** @var Application */
	protected $app;

	protected $bootstraps = [
		\RPC\Bootstraps\Environment::class, // Keep this first
		\RPC\Bootstraps\Database::class,
		\RPC\Bootstraps\Session::class,
		\RPC\Bootstraps\Errors::class,
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
		$this->router->run();
	}
}