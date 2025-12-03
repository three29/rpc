<?php

namespace RPC\Bootstraps;

use RPC\Contracts\Bootstrap;
use RPC\Db;

class Database implements Bootstrap {
	public static function handle() {
		Db::addConnection( 'default', array(
			'adapter'  => getenv( 'DB_ADAPTER' ),
			'hostname' => getenv( 'DB_HOSTNAME' ),
			'database' => getenv( 'DB_NAME' ),
			'socket'   => getenv( 'DB_SOCKET' ),
			'port'     => getenv( 'DB_PORT' ),
			'username' => getenv( 'DB_USERNAME' ),
			'password' => getenv( 'DB_PASSWORD' ),
			'prefix'   => getenv( 'DB_PREFIX' )
		));
	}
}