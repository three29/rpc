<?php

namespace RPC\Bootstraps;

use RPC\Contracts\Bootstrap;
use RPC\Db;

class Database implements Bootstrap {
	public static function handle() {
		// Get database configuration using env() helper
		$dbName = env('DB_NAME');
		$dbAdapter = env('DB_ADAPTER');
		$appEnv = env('APP_ENV');

		// Skip database setup in testing environment if DB_NAME is not set
		// This allows tests to run without requiring a database connection
		if ($appEnv === 'testing' && empty($dbName)) {
			return;
		}

		// Skip if no database configuration is present (optional database)
		// If you require a database, remove this check
		if (empty($dbName) && empty($dbAdapter)) {
			return;
		}

		// If DB_NAME is set but empty/false, throw helpful error
		if (isset($_ENV['DB_NAME']) && empty($dbName)) {
			throw new \RuntimeException('DB_NAME is set in .env but is empty. Check your .env file.');
		}

		Db::addConnection( 'default', array(
			'adapter'  => env('DB_ADAPTER'),
			'hostname' => env('DB_HOSTNAME'),
			'database' => $dbName,
			'socket'   => env('DB_SOCKET'),
			'port'     => env('DB_PORT'),
			'username' => env('DB_USERNAME'),
			'password' => env('DB_PASSWORD'),
			'prefix'   => env('DB_PREFIX', '')
		));
	}
}