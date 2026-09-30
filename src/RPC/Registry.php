<?php

namespace RPC;

use Psr\Container\ContainerInterface;

/**
 * Service registry with PSR-11 adapter support
 * Now uses Application container under the hood
 *
 * Allows registering objects which can be accessed from anywhere in the
 * application
 *
 * <code>
 *
 * // boostrap file - backward compatible static usage
 *
 * Registry::set( 'mno', new MuchNeededObject() );
 * $mno = Registry::get( 'mno' );  // Returns null if not found
 *
 * // PSR-11 compliant usage (recommended for new code)
 * $container = Registry::container();
 * $mno = $container->get('mno');  // Throws NotFoundException if not found
 * if ($container->has('mno')) { ... }
 *
 * </code>
 *
 * @package Core
 */
class Registry
{
	/**
	 * Get the application container instance
	 *
	 * @return Application|null
	 */
	protected static function getAppContainer(): ?Application
	{
		return Application::$app ?? null;
	}

	/**
	 * Registers an object into the container and returns the object
	 *
	 * @param string $name
	 * @param mixed $obj
	 *
	 * @return mixed
	 */
	public static function set(string $name, mixed $obj): mixed
	{
		$app = self::getAppContainer();

		// If Application container exists, use it
		if ($app) {
			return $app->instance($name, $obj);
		}

		// Fallback: store in global registry (for bootstrap before app exists)
		$GLOBALS['_RPC_REGISTRY_'][$name] = $obj;
		return $obj;
	}

	/**
	 * Fetches an object from the container
	 * Returns null if not found for backward compatibility
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public static function get(string $name): mixed
	{
		$app = self::getAppContainer();

		// Try Application container first
		if ($app && $app->has($name)) {
			return $app->make($name);
		}

		// Fallback to global registry
		return $GLOBALS['_RPC_REGISTRY_'][$name] ?? null;
	}

	/**
	 * Determines if a given key has been registered
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function registered(string $name): bool
	{
		$app = self::getAppContainer();

		// Check Application container first
		if ($app && $app->has($name)) {
			return true;
		}

		// Check global registry fallback
		return isset($GLOBALS['_RPC_REGISTRY_'][$name]);
	}

	/**
	 * Get PSR-11 compliant container adapter
	 * Returns Application container if available, otherwise legacy adapter
	 *
	 * @return ContainerInterface
	 */
	public static function container(): ContainerInterface
	{
		$app = self::getAppContainer();

		if ($app) {
			return $app;
		}

		// Legacy fallback adapter
		return new Registry\ContainerAdapter();
	}
}
