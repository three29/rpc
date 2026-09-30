<?php

namespace RPC\Db;

use RPC\Signal;
use RPC\Db;

/**
 * Database adapter base class. This is meant to be extended by all database
 * drivers
 *
 * @package Db
 */
abstract class Adapter
{

	/**
	 * Resource holding the connections to the database
	 *
	 * @var \PDO|null
	 */
	protected $_rpc_handle = null;

	/**
	 * Table name prefix
	 *
	 * @var string
	 */
	protected $_rpc_prefix = '';

	/**
	 * Default fetch mode
	 *
	 * @var int
	 */
	protected $_rpc_fetchmode = \RPC\Db::FETCH_ASSOC;

	/**
	 * Number of affected rows by the last statement
	 *
	 * @var int
	 */
	protected $_rpc_affectedrows = 0;

	/**
	 * Flag to prevent infinite recursion during query logging
	 *
	 * @var bool
	 */
	protected static $_rpc_logging = false;

	/**
	 * Storage for executed queries when DEBUG_QUERIES is enabled
	 *
	 * @var array<string>
	 */
	protected array $_rpc_queries = [];


	/**
	 * Tries to connect to the database, throwing an exception if it fails
	 *
	 * @param string $username
	 * @param string $password
	 * @param array  $options
	 */
	abstract public function connect( string $username, string $password, mixed $options = null ): mixed;

	/**
	 * Return the last autoincremented values
	 *
	 * @return int
	 */
	abstract public function getLastId(): mixed;

	/**
	 * Returns the database handle
	 *
	 * @return \PDO|null
	 */
	public function getHandle(): ?\PDO
	{
		return $this->_rpc_handle;
	}

	/**
	 * Sets the database handle
	 *
	 * @param \PDO $handle
	 */
	protected function setHandle( \PDO $handle ): void
	{
		$this->_rpc_handle = $handle;
	}

	/**
	 * Set a connection attribute
	 *
	 * @param int   $attribute
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function setAttribute( int $attribute, mixed $value ): bool
	{
		return $this->getHandle()->setAttribute( $attribute, $value );
	}

	/**
	 * Sets the prefix of the tables in this database
	 *
	 * @param string $prefix
	 */
	public function setPrefix( string $prefix ): void
	{
		$this->_rpc_prefix = $prefix;
	}

	/**
	 * Returns the prefix of the tables in the database
	 *
	 * @return string
	 */
	public function getPrefix(): string
	{
		return $this->_rpc_prefix;
	}

	/**
	 * Sets a prefered fetch mode for all results
	 *
	 * @param int $mode
	 */
	public function setFetchMode( int $mode ): void
	{
		$this->_rpc_fetchmode = $mode;
	}

	/**
	 * Returns the default fetch mode
	 *
	 * @return int
	 */
	public function getFetchMode(): int
	{
		return $this->_rpc_fetchmode;
	}

	/**
	 * Executes a statement and returns the number of affected rows
	 *
	 * @param string $sql
	 *
	 * @return int
	 */
	public function execute( string $sql ): int|false
	{
		$event = new \RPC\Events\QueryExecuting($sql, 'statement');
		\RPC\Signal::getInstance()->dispatch($event);

		if ($event->isPropagationStopped()) {
			return 0;
		}

		$this->addQuery( $sql );

		$this->_rpc_affectedrows = $this->getHandle()->exec( $sql );

		$this->logQuery( $sql );

		\RPC\Signal::getInstance()->dispatch(new \RPC\Events\QueryExecuted($sql, 'statement'));

		return $this->_rpc_affectedrows;
	}

	/**
	 * Queries the database
	 *
	 * @param string $sql
	 *
	 * @return array|null
	 */
	public function query( string $sql ): ?array
	{
		$event = new \RPC\Events\QueryExecuting($sql, 'query');
		\RPC\Signal::getInstance()->dispatch($event);

		if ($event->isPropagationStopped()) {
			return null;
		}

		$this->addQuery( $sql );

		$res = $this->getHandle()->query( $sql, $this->getFetchMode() );

		$this->logQuery( $sql );

		\RPC\Signal::getInstance()->dispatch(new \RPC\Events\QueryExecuted($sql, 'query'));

		return $res->fetchAll();
	}

	/**
	 * Returns the number of affected rows by a previous insert, update, delete
	 * or truncate query
	 *
	 * @return int
	 */
	public function getAffectedRows(): int
	{
		 return $this->_rpc_affectedrows;
	}

	/**
	 * Set the default charset for the connection
	 *
	 * @param string $charset
	 */
	abstract public function setCharset( ?string $charset = null ): mixed;

	/**
	 * Prepares a query for execution. Returns a statement
	 *
	 * @return \RPC\Db\Statement
	 */
	abstract public function prepare( string $sql, mixed $options = null ): \RPC\Db\Statement;

	/**
	 * Open transaction depth per PDO connection. The connection is shared
	 * between adapter instances (see the Registry), so depth is tracked on the
	 * handle rather than on the adapter.
	 *
	 * @var \WeakMap<\PDO, int>|null
	 */
	protected static ?\WeakMap $_rpc_transaction_levels = null;

	/**
	 * Starts a transaction. Calls nest: inside an open transaction a savepoint
	 * is created instead, so an inner rollback only undoes the inner work and
	 * nothing is committed until the outermost commit.
	 *
	 * @return bool
	 */
	public function beginTransaction(): bool
	{
		$handle = $this->getHandle();
		$level  = $this->getTransactionLevel();

		if( $level === 0 )
		{
			$handle->beginTransaction();
		}
		else
		{
			$handle->exec( $this->savepointSql( 'create', $level ) );
		}

		static::transactionLevels()[ $handle ] = $level + 1;

		return true;
	}

	/**
	 * Commits the transaction, or releases the savepoint of a nested one
	 *
	 * @return bool
	 */
	public function commit(): bool
	{
		$handle = $this->getHandle();
		$level  = $this->getTransactionLevel();

		if( $level <= 1 )
		{
			$this->setTransactionLevel( 0 );

			return $handle->commit();
		}

		$this->setTransactionLevel( $level - 1 );
		$sql = $this->savepointSql( 'release', $level - 1 );
		if( $sql !== '' )
		{
			$handle->exec( $sql );
		}

		return true;
	}

	/**
	 * Rolls back the transaction, or only the work since the matching
	 * beginTransaction() of a nested one
	 *
	 * @return bool
	 */
	public function rollback(): bool
	{
		$handle = $this->getHandle();
		$level  = $this->getTransactionLevel();

		if( $level <= 1 )
		{
			$this->setTransactionLevel( 0 );

			return $handle->rollBack();
		}

		$this->setTransactionLevel( $level - 1 );
		$handle->exec( $this->savepointSql( 'rollback', $level - 1 ) );

		return true;
	}

	/**
	 * Runs $callback in a transaction: commits when it returns, rolls back and
	 * re-throws when it throws. Can be nested.
	 *
	 * @param callable $callback Receives this adapter
	 *
	 * @return mixed The callback's return value
	 */
	public function transaction( callable $callback ): mixed
	{
		$this->beginTransaction();

		try
		{
			$result = $callback( $this );
		}
		catch( \Throwable $e )
		{
			if( $this->inTransaction() )
			{
				$this->rollback();
			}

			throw $e;
		}

		$this->commit();

		return $result;
	}

	public function inTransaction(): bool
	{
		return $this->getTransactionLevel() > 0;
	}

	public function getTransactionLevel(): int
	{
		$handle = $this->getHandle();

		if( ! $handle )
		{
			return 0;
		}

		$levels = static::transactionLevels();
		$level  = isset( $levels[ $handle ] ) ? $levels[ $handle ] : 0;

		// A transaction ended outside the adapter (e.g. an implicit commit from
		// DDL, or direct PDO use) resets the depth
		if( $level > 0 && ! $handle->inTransaction() )
		{
			$this->setTransactionLevel( 0 );

			return 0;
		}

		return $level;
	}

	protected function setTransactionLevel( int $level ): void
	{
		static::transactionLevels()[ $this->getHandle() ] = $level;
	}

	protected static function transactionLevels(): \WeakMap
	{
		return static::$_rpc_transaction_levels ??= new \WeakMap();
	}

	/**
	 * SQL for a nested-transaction savepoint. Standard SQL, as used by MySQL,
	 * PostgreSQL and SQLite; adapters for other databases override it.
	 *
	 * @param string $action create|release|rollback
	 * @param int    $level  Depth the savepoint belongs to (1 = first nested level)
	 *
	 * @return string SQL to run, or '' when the action is not needed
	 */
	protected function savepointSql( string $action, int $level ): string
	{
		$name = 'rpc_savepoint_' . $level;

		switch( $action )
		{
			case 'create':
				return 'SAVEPOINT ' . $name;
			case 'release':
				return 'RELEASE SAVEPOINT ' . $name;
			case 'rollback':
				return 'ROLLBACK TO SAVEPOINT ' . $name;
		}

		throw new \InvalidArgumentException( 'Unknown savepoint action: ' . $action );
	}

	/**
	 * Returns the code of the last error
	 *
	 * @return string|null
	 */
	public function getErrorCode(): ?string
	{
		return $this->getHandle()->errorCode();
	}

	/**
	 * Returns info about the error
	 *
	 * @return array
	 */
	public function getErrorInfo(): array
	{
		return $this->getHandle()->errorInfo();
	}

	/**
	 * Disconnects from the server, freeing up resources
	 */
	public function disconnect(): void
	{
		$this->_rpc_handle = null;
	}

	/**
	 * Cleaning up the object
	 */
	public function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * Add a query to the debug query log
	 *
	 * @param string $sql
	 * @return void
	 */
	public function addQuery( string $sql ): void
	{
		if ( env( 'DEBUG_QUERIES' ) === true ) {
			$this->_rpc_queries[] = $sql;
		}
	}

	/**
	 * Get logged queries
	 *
	 * @param bool $all If true, return all queries; if false, return only the last query
	 * @return mixed
	 */
	public function getQueries( bool $all = false ): mixed
	{
		if( ! env( 'DEBUG_QUERIES' ) )
		{
			return 'DEBUG_QUERIES variable is not defined in .env file.';
		}
		return ( $all ? $this->_rpc_queries : ( end( $this->_rpc_queries ) ?: null ) );
	}

	/**
	 * Log a query to the query_logger table
	 * Protected against infinite recursion
	 *
	 * @param string $sql
	 * @return void
	 */
	protected function logQuery( string $sql ): void
	{
		// Prevent infinite recursion
		if( self::$_rpc_logging || env( 'LOG_QUERIES' ) !== true )
		{
			return;
		}

		// Don't log the query logger inserts themselves
		if( stripos( $sql, 'query_logger' ) !== false )
		{
			return;
		}

		try
		{
			self::$_rpc_logging = true;
			$this->getHandle()
				->prepare( "INSERT INTO query_logger (query, ip, created) VALUES (?, ?, ?)" )
				->execute( array( $sql, \RPC\Util::get_client_source(), date( 'Y-m-d H:i:s' ) ) );
		}
		catch( \Exception $e )
		{
			// Silently fail if logging fails - we don't want to break the application
			// due to query logging issues
		}
		finally
		{
			self::$_rpc_logging = false;
		}
	}

}

?>
