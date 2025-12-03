<?php

namespace RPC;

class Application {

	static protected $app;

	public static function configure($root_path)
	{
		if (empty($root_path)) {
			throw new \RuntimeException('Root path not set');
		}

		\RPC\Registry::set('root_path', $root_path);
		if (!empty(static::$app)) {
			return static::$app;
		}

		static::setupEnvironment($root_path);
		return (static::$app = new static());
	}

	protected static function setupEnvironment($root_path) {
		$dotenv = \Dotenv\Dotenv::createImmutable( $root_path . '/config' );
		$dotenv->safeLoad(); // Use safeLoad to not throw if .env doesn't exist

		//set some default constants if they aren't defined
		if( ! defined( 'APP_PATH' ) )
		{
			define( 'APP_PATH', $root_path . '/APP' );
		}

		if( ! defined( 'CACHE_PATH' ) )
		{
			define( 'CACHE_PATH', $root_path . '/tmp/cache' );
		}
	}

	/**
	 *
	 * @return static
	 */
	public function create() {
		return static::$app;
	}
}