<?php

namespace RPC\Bootstraps;

use RPC\Contracts\Bootstrap;

class Environment implements Bootstrap {

	public static function handle()
	{
		$root_path = \RPC\Registry::get('root_path');
		if (empty($root_path)) {
			throw new \RuntimeException('Root path not set');
		}

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
}