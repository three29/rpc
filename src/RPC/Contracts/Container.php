<?php

namespace RPC\Contracts;

use Psr\Container\ContainerInterface;
use Closure;

/**
 * Container Contract extending PSR-11
 *
 * Provides Laravel-inspired dependency injection container methods
 * while maintaining PSR-11 compliance
 */
interface Container extends ContainerInterface
{
	/**
	 * {@inheritdoc}
	 *
	 * @template TClass of object
	 *
	 * @param  string|class-string<TClass>  $id
	 * @return ($id is class-string<TClass> ? TClass : mixed)
	 */
	public function get(string $id);

	/**
	 * Bind a value or resolver into the container
	 *
	 * @param string $abstract
	 * @param mixed $concrete
	 * @param bool $shared
	 * @return void
	 */
	public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void;

	/**
	 * Register a shared binding (singleton) in the container
	 *
	 * @param string $abstract
	 * @param mixed $concrete
	 * @return void
	 */
	public function singleton(string $abstract, mixed $concrete = null): void;

	/**
	 * Register an existing instance as shared in the container
	 *
	 * @param string $abstract
	 * @param mixed $instance
	 * @return mixed
	 */
	public function instance(string $abstract, mixed $instance): mixed;

	/**
	 * Resolve a value from the container
	 * Alias for get() but allows default value
	 *
	 * @param string $abstract
	 * @param mixed $default
	 * @return mixed
	 */
	public function make(string $abstract, mixed $default = null): mixed;

	/**
	 * Determine if a given type has been bound
	 * Alias for has() for Laravel compatibility
	 *
	 * @param string $abstract
	 * @return bool
	 */
	public function bound(string $abstract): bool;

	/**
	 * Remove a resolved instance from the container
	 *
	 * @param string $abstract
	 * @return void
	 */
	public function forget(string $abstract): void;

	/**
	 * Flush all bindings and resolved instances
	 *
	 * @return void
	 */
	public function flush(): void;
}