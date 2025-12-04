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

		if( getenv('DEBUG_QUERIES') === "true" )
		{
			/** @phpstan-ignore-next-line Dynamic property for query debugging */
			$this->getHandle()->_queries[] = $sql;
		}

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

		if( getenv('DEBUG_QUERIES') === "true" )
		{
			/** @phpstan-ignore-next-line Dynamic property for query debugging */
			$this->getHandle()->_queries[] = $sql;
		}

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
	 * Starts a new transaction
	 *
	 * @return bool
	 */
	public function beginTransaction(): bool
	{
		return $this->getHandle()->beginTransaction();
	}

	/**
	 * Commits all the queries and statements in the transaction
	 *
	 * @return bool
	 */
	public function commit(): bool
	{
		return $this->getHandle()->commit();
	}

	/**
	 * Rolls back queries and statement in the transaction
	 *
	 * @return bool
	 */
	public function rollback(): bool
	{
		return $this->getHandle()->rollBack();
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


	public function getQueries( bool $all = false ): mixed
	{
		if( ! getenv( 'DEBUG_QUERIES' ) )
		{
			return 'DEBUG_QUERIES variable is not defined in .env file.';
		}
		/** @phpstan-ignore-next-line Dynamic property for query debugging */
		return ( $all ? $this->getHandle()->_queries : end( $this->getHandle()->_queries ) );
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
		if( self::$_rpc_logging || getenv( 'LOG_QUERIES' ) !== "true" )
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
