<?php

namespace RPC\Bootstraps;

use RPC\Contracts\Bootstrap;
use RPC\Db;

class Database implements Bootstrap {
	public static function handle() {
		// Get database configuration - use $_ENV first (Dotenv v5), fallback to getenv()
		$dbName = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: null;
		$dbAdapter = $_ENV['DB_ADAPTER'] ?? getenv('DB_ADAPTER') ?: null;
		$appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: null;

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
			'adapter'  => $_ENV['DB_ADAPTER'] ?? getenv( 'DB_ADAPTER' ) ?: null,
			'hostname' => $_ENV['DB_HOSTNAME'] ?? getenv( 'DB_HOSTNAME' ) ?: null,
			'database' => $dbName,
			'socket'   => $_ENV['DB_SOCKET'] ?? getenv( 'DB_SOCKET' ) ?: null,
			'port'     => $_ENV['DB_PORT'] ?? getenv( 'DB_PORT' ) ?: null,
			'username' => $_ENV['DB_USERNAME'] ?? getenv( 'DB_USERNAME' ) ?: null,
			'password' => $_ENV['DB_PASSWORD'] ?? getenv( 'DB_PASSWORD' ) ?: null,
			'prefix'   => $_ENV['DB_PREFIX'] ?? getenv( 'DB_PREFIX' ) ?: ''
		));
	}
}