<?php

namespace RPC;

use RPC\Contracts\Container as ContainerContract;
use RPC\Exception\ConfigurationException;
use Psr\Container\NotFoundExceptionInterface;
use Closure;

class Application implements ContainerContract
{
	static public $app;

	/**
	 * Container bindings
	 *
	 * @var array
	 */
	protected array $bindings = [];

	/**
	 * Container resolved instances (singletons)
	 *
	 * @var array
	 */
	protected array $instances = [];

	/**
	 * Configure and bootstrap the application
	 *
	 * @param string $root_path
	 * @return static
	 */
	public static function configure(string $root_path): static
	{
		if (empty($root_path)) {
			throw new ConfigurationException('Root path not set');
		}

		\RPC\Registry::set('root_path', $root_path);
		if (!empty(static::$app)) {
			return static::$app;
		}

		static::setupEnvironment($root_path);
		/** @phpstan-ignore-next-line Singleton pattern - child classes must have parameterless constructors */
		$app = new static();

		// Register the application instance in itself
		$app->instance(ContainerContract::class, $app);
		$app->instance(Application::class, $app);
		$app->instance('app', $app);

		// Register core framework services
		$app->registerCoreServices();

		return (static::$app = $app);
	}

	protected static function setupEnvironment(string $root_path): void
	{
		$dotenv = \Dotenv\Dotenv::createImmutable($root_path . '/config');
		$dotenv->safeLoad(); // Use safeLoad to not throw if .env doesn't exist

		//set some default constants if they aren't defined
		if (!defined('APP_PATH')) {
			define('APP_PATH', $root_path . '/APP');
		}

		if (!defined('CACHE_PATH')) {
			define('CACHE_PATH', $root_path . '/tmp/cache');
		}
	}

	/**
	 * Get the application instance
	 *
	 * @return static
	 */
	public function create(): static
	{
		return static::$app;
	}

	/**
	 * PSR-11: Get an entry from the container
	 *
	 * @param string $id
	 * @return mixed
	 * @throws NotFoundExceptionInterface
	 */
	public function get(string $id): mixed
	{
		// Check if we have a resolved instance
		if (isset($this->instances[$id])) {
			return $this->instances[$id];
		}

		// Check if we have a binding
		if (!isset($this->bindings[$id])) {
			throw new Registry\NotFoundException("Entry '{$id}' not found in container");
		}

		$binding = $this->bindings[$id];

		// Resolve the binding
		$concrete = $binding['concrete'];

		if ($concrete instanceof Closure) {
			$resolved = $concrete($this);
		} else {
			$resolved = $concrete;
		}

		// Store as instance if shared
		if ($binding['shared']) {
			$this->instances[$id] = $resolved;
		}

		return $resolved;
	}

	/**
	 * PSR-11: Check if container has an entry
	 *
	 * @param string $id
	 * @return bool
	 */
	public function has(string $id): bool
	{
		return isset($this->bindings[$id]) || isset($this->instances[$id]);
	}

	/**
	 * Bind a value or resolver into the container
	 *
	 * @param string $abstract
	 * @param mixed $concrete
	 * @param bool $shared
	 * @return void
	 */
	public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void
	{
		// If no concrete given, use the abstract as concrete
		if (is_null($concrete)) {
			$concrete = $abstract;
		}

		$this->bindings[$abstract] = [
			'concrete' => $concrete,
			'shared' => $shared
		];
	}

	/**
	 * Register a shared binding (singleton) in the container
	 *
	 * @param string $abstract
	 * @param mixed $concrete
	 * @return void
	 */
	public function singleton(string $abstract, mixed $concrete = null): void
	{
		$this->bind($abstract, $concrete, true);
	}

	/**
	 * Register an existing instance as shared in the container
	 *
	 * @param string $abstract
	 * @param mixed $instance
	 * @return mixed
	 */
	public function instance(string $abstract, mixed $instance): mixed
	{
		$this->instances[$abstract] = $instance;
		return $instance;
	}

	/**
	 * Resolve a value from the container
	 * Alias for get() but allows default value
	 *
	 * @param string $abstract
	 * @param mixed $default
	 * @return mixed
	 */
	public function make(string $abstract, mixed $default = null): mixed
	{
		try {
			return $this->get($abstract);
		} catch (NotFoundExceptionInterface $e) {
			return $default;
		}
	}

	/**
	 * Determine if a given type has been bound
	 * Alias for has() for Laravel compatibility
	 *
	 * @param string $abstract
	 * @return bool
	 */
	public function bound(string $abstract): bool
	{
		return $this->has($abstract);
	}

	/**
	 * Remove a resolved instance from the container
	 *
	 * @param string $abstract
	 * @return void
	 */
	public function forget(string $abstract): void
	{
		unset($this->bindings[$abstract], $this->instances[$abstract]);
	}

	/**
	 * Flush all bindings and resolved instances
	 *
	 * @return void
	 */
	public function flush(): void
	{
		$this->bindings = [];
		$this->instances = [];
	}

	/**
	 * Register core framework services in the container
	 *
	 * @return void
	 */
	protected function registerCoreServices(): void
	{
		// Register Signal/EventDispatcher as singleton
		$this->singleton(\Psr\EventDispatcher\EventDispatcherInterface::class, function() {
			return Signal::getInstance();
		});
		$this->singleton(Signal::class, function() {
			return Signal::getInstance();
		});

		// Register HTTP Request as singleton
		$this->singleton(\RPC\HTTP\Request::class, function() {
			return new \RPC\HTTP\Request();
		});
		$this->singleton('request', function($app) {
			return $app->make(\RPC\HTTP\Request::class);
		});

		// Register HTTP Response as singleton
		$this->singleton(\RPC\HTTP\Response::class, function() {
			return new \RPC\HTTP\Response();
		});
		$this->singleton('response', function($app) {
			return $app->make(\RPC\HTTP\Response::class);
		});

		// Register Session as singleton
		$this->singleton(Session::class, function() {
			return new Session();
		});
		$this->singleton('session', function($app) {
			return $app->make(Session::class);
		});

		// Register Router (will be bound per-request in actual usage)
		$this->bind(Router::class, function() {
			return new Router();
		});
		$this->bind('router', function($app) {
			return $app->make(Router::class);
		});
	}
}