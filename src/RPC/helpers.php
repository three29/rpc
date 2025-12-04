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
