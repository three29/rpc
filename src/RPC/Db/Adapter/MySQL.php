<?php

namespace RPC\Db\Adapter;


use RPC\Db\Adapter; 
use RPC\Db\Statement;
use PDO;


/**
 * MySQLi Adapter implementation
 * 
 * @package Db
 */
class MySQL extends Adapter
{
	
	/**
	 * Database hostname
	 *
	 * @var string
	 */
	protected $_rpc_hostname = 'localhost';

	/**
	 * Database name
	 *
	 * @var string
	 */
	protected $_rpc_database = '';

	/**
	 * Server socket location
	 *
	 * @var string
	 */
	protected $_rpc_socket = '';

	/**
	 * Server's listening port
	 *
	 * @var int
	 */
	protected $_rpc_port = null;

	/**
	 * Connection credentials stored for lazy connection
	 *
	 * @var array|null
	 */
	protected $_rpc_credentials = null;

	/**
	 * Flag to track if connection has been established
	 *
	 * @var bool
	 */
	protected $_rpc_connected = false;
	
	/**
	 * Class constructor
	 *
	 * @param string $hostname
	 * @param string $database
	 * @param string $socket
	 * @param int $port
	 */
	public function __construct( string $hostname = 'localhost', ?string $database = null, ?string $socket = null, int $port = 3306 )
	{
		$this->_rpc_hostname = $hostname;
		$this->_rpc_database = $database;
		$this->_rpc_socket   = $socket;
		$this->_rpc_port     = $port;
	}
	
	/**
	 * Attempts to connect to the database, throwing an exception if it fails
	 *
	 * @param string $username
	 * @param string $password
	 * @param array  $options
	 *
	 * @return static
	 */
	public function connect( string $username, string $password, mixed $options = [] ): static
	{
		// Store credentials for lazy connection
		$this->_rpc_credentials = [
			'username' => $username,
			'password' => $password,
			'options' => $options
		];

		return $this;
	}

	/**
	 * Establishes the actual database connection (called lazily)
	 *
	 * @return void
	 */
	protected function ensureConnected(): void
	{
		if( $this->_rpc_connected )
		{
			return;
		}

		if( ! $this->_rpc_credentials )
		{
			throw new \Exception( 'Connection credentials not set. Call connect() first.' );
		}

		$username = $this->_rpc_credentials['username'];
		$password = $this->_rpc_credentials['password'];
		$options = $this->_rpc_credentials['options'];

		// Check if connection exists in Registry instead of GLOBALS
		$connection_key = 'db_connection_' . md5( $this->_rpc_hostname . $this->_rpc_database );

		if( ! \RPC\Registry::registered( $connection_key ) )
		{
			if( $this->_rpc_socket )
			{
				$dsn = 'mysql:unix_socket=' . $this->_rpc_socket . ';dname=' . $this->_rpc_database;
			}
			else
			{
				$dsn = 'mysql:host=' . $this->_rpc_hostname . ';dbname=' . $this->_rpc_database;
			}

			$dboptions = [];

			if( ! empty( $options['sql_mode'] ) ) {
				$dboptions[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET sql_mode="' . $options['sql_mode'] . '"';
			}

			$pdo = new \PDO( $dsn, $username, $password, $dboptions );
			\RPC\Registry::set( $connection_key, $pdo );
		}

		$handle = \RPC\Registry::get( $connection_key );
		$this->setHandle( $handle );
		$handle->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
		$handle->setAttribute( \PDO::ATTR_CASE, \PDO::CASE_LOWER );
		$handle->setAttribute( \PDO::ATTR_AUTOCOMMIT, true );

		$this->_rpc_connected = true;
	}

	/**
	 * Returns the database handle, ensuring connection is established
	 *
	 * @return PDO
	 */
	public function getHandle(): ?\PDO
	{
		$this->ensureConnected();
		return parent::getHandle();
	}

	/**
	 * Overiding the default implementation as it seems to have a bug, at least
	 * with MySQL
	 *
	 * @return int
	 */
	public function getLastId(): mixed
	{
		$sql = 'select last_insert_id() as n';
		$res = $this->query( $sql );

		return $res[0]['n'];
	}
	
	/**
	 * Returns the number of rows found by the last query containing the
	 * SQL_CALC_FOUND_ROWS operator
	 *
	 * @return int
	 */
	public function getFoundRows(): mixed
	{
		$res = $this->getHandle()->query( 'select found_rows() as f' );
		$row = $res->fetch();
		return $row['f'];
	}
	
	/**
	 * Set the default charset for the connection
	 *
	 * @param string $charset
	 *
	 * @return int|false
	 */
	public function setCharset( ?string $charset = 'utf8' ): int|false
	{
		return $this->getHandle()->exec( 'set charset ' . $charset );
	}
	
	/**
	 * Prepares a query and returns a new statement
	 *
	 * @param string $sql
	 * @param array  $options
	 *
	 * @return \RPC\Db\Statement
	 */
	public function prepare( string $sql, mixed $options = array() ): \RPC\Db\Statement
	{
		return new \RPC\Db\Statement( $sql, $this, $options );
	}

	
}

?>
