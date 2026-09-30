<?php

/**
 * Global helper functions for dependency injection
 */

if (!function_exists('app')) {
	/**
	 * Get the Application container instance or resolve a service
	 *
	 * @param string|null $abstract Service name to resolve
	 * @param mixed $default Default value if service not found
	 * @return mixed
	 */
	function app(?string $abstract = null, mixed $default = null): mixed
	{
		if (is_null($abstract)) {
			return \RPC\Application::$app;
		}

		return \RPC\Application::$app?->make($abstract, $default);
	}
}

if (!function_exists('request')) {
	/**
	 * Get the Request instance from the container
	 *
	 * @return \RPC\HTTP\Request
	 */
	function request(): \RPC\HTTP\Request
	{
		return app(\RPC\HTTP\Request::class);
	}
}

if (!function_exists('response')) {
	/**
	 * Get the Response instance from the container
	 *
	 * @return \RPC\HTTP\Response
	 */
	function response(): \RPC\HTTP\Response
	{
		return app(\RPC\HTTP\Response::class);
	}
}

if (!function_exists('session')) {
	/**
	 * Get the Session instance from the container
	 *
	 * @return \RPC\Session
	 */
	function session(): \RPC\Session
	{
		return app(\RPC\Session::class);
	}
}

if (!function_exists('events')) {
	/**
	 * Get the EventDispatcher instance from the container
	 *
	 * @return \Psr\EventDispatcher\EventDispatcherInterface
	 */
	function events(): \Psr\EventDispatcher\EventDispatcherInterface
	{
		return app(\Psr\EventDispatcher\EventDispatcherInterface::class);
	}
}

if (!function_exists('dispatch')) {
	/**
	 * Dispatch an event
	 *
	 * @param object $event
	 * @return object
	 */
	function dispatch(object $event): object
	{
		return events()->dispatch($event);
	}
}

if (!function_exists('env')) {
	/**
	 * Get an environment variable value
	 *
	 * Checks $_ENV first, then $_SERVER, then falls back to getenv()
	 * Converts string representations to proper types:
	 * - 'true', '(true)' => true
	 * - 'false', '(false)' => false
	 * - 'null', '(null)' => null
	 * - 'empty', '(empty)' => ''
	 *
	 * @param string $key Environment variable name
	 * @param mixed $default Default value if not found
	 * @return mixed
	 */
	function env(string $key, mixed $default = null): mixed
	{
		// Check $_ENV first (Dotenv v5 priority)
		if (isset($_ENV[$key])) {
			return _env_convert_value($_ENV[$key]);
		}

		// Check $_SERVER
		if (isset($_SERVER[$key])) {
			return _env_convert_value($_SERVER[$key]);
		}

		// Fall back to getenv() for compatibility
		$value = getenv($key);
		if ($value !== false) {
			return _env_convert_value($value);
		}

		// Return default if not found
		return $default;
	}
}

if (!function_exists('_env_convert_value')) {
	/**
	 * Convert environment variable string values to proper types
	 *
	 * @internal
	 * @param mixed $value
	 * @return mixed
	 */
	function _env_convert_value(mixed $value): mixed
	{
		if (!is_string($value)) {
			return $value;
		}

		$lower = strtolower($value);

		return match ($lower) {
			'true', '(true)' => true,
			'false', '(false)' => false,
			'null', '(null)' => null,
			'empty', '(empty)' => '',
			default => $value,
		};
	}
}
