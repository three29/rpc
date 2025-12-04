<?php

namespace RPC\Db;

use ReflectionException;
use RPC\Db;
use RPC\Db\Adapter;
use RPC\Signal;

/**
 * Class representing a prepared query. It is not meant to be instantiated in
 * code, instead, an instance will be returned each time one executes
 * <code>\RPC\Db\Adapter->prepare( $sql )</code>
 * 
 * @package Db
 */
class Statement
{
	
	/**
	 * Database handle
	 *
	 * @var Adapter
	 */
	protected $db;
	
	/**
	 * Query to be executed
	 * 
	 * @var string
	 */
	protected $sql;
	
	/**
	 * Statement
	 *
	 * @var \PDOStatement|null
	 */
	protected $stmt;
	
	/**
	 * Prepares the query
	 *
	 * @param string         $sql
	 * @param \RPC\Db\Adapter $db
	 * @param array          $options
	 */
	public function __construct( string $sql, \RPC\Db\Adapter $db, mixed $options = array() )
	{
		$this->db  = $db;
		$this->sql = $sql;
		
		$this->stmt = $this->db->getHandle()->prepare( $sql, $options );
		$this->stmt->setFetchMode( $this->db->getFetchMode() );
	}
	
	/**
	 * Sets how rows in the result should be returned
	 *
	 * @param int $fetchmode
	 *
	 * @return self
	 */
	public function setFetchMode( int $fetchmode ): self
	{
		$this->stmt->setFetchMode( $fetchmode );
		return $this;
	}

	/**
	 * Executes a query and returns the result
	 *
	 * @param array $params Parameters that should be replaced in the query
	 *
	 * @return array|bool|null
	 * @throws ReflectionException
	 */
	public function execute( array $params = array() ): array|bool|null
	{
		$event = new \RPC\Events\QueryExecuting($this->sql, 'prepared');
		\RPC\Signal::getInstance()->dispatch($event);

		if ($event->isPropagationStopped()) {
			return null;
		}

		// Build debug SQL string with properly quoted parameters
		$debugSql = $this->sql;
		if( getenv( 'DEBUG_QUERIES' ) === "true" || getenv( 'LOG_QUERIES' ) === "true" )
		{
			$debugSql = $this->buildDebugSql( $this->sql, $params );
		}

		if( getenv( 'DEBUG_QUERIES' ) === "true" )
		{
			/** @phpstan-ignore-next-line Dynamic property for query debugging */
			$this->db->getHandle()->_queries[] = $debugSql;
		}

		$res = $this->stmt->execute( $params );

		// Use reflection to access protected logQuery method from Adapter
		if( getenv( 'LOG_QUERIES' ) === "true" )
		{
			$reflection = new \ReflectionClass( $this->db );
			$method = $reflection->getMethod( 'logQuery' );
			$method->setAccessible( true );
			$method->invoke( $this->db, $debugSql );
		}

		\RPC\Signal::getInstance()->dispatch(new \RPC\Events\QueryExecuted($this->sql, 'prepared'));

		if( $res )
		{
			if( stripos( trim( $this->sql ), 'select' ) === 0 )
			{
				$rows = $this->stmt->fetchAll();
				$this->stmt->closeCursor();

				return $rows;
			}

			return true;
		}

		return false;
	}

	/**
	 * Build a debug SQL string with parameters substituted
	 * This is for display/logging only, not for execution
	 *
	 * @param string $sql
	 * @param array $params
	 * @return string
	 */
	protected function buildDebugSql( string $sql, array $params ): string
	{
		$debugSql = $sql;
		foreach( $params as $param )
		{
			// Properly quote the parameter based on type
			if( is_null( $param ) )
			{
				$quotedParam = 'NULL';
			}
			elseif( is_bool( $param ) )
			{
				$quotedParam = $param ? '1' : '0';
			}
			elseif( is_numeric( $param ) )
			{
				$quotedParam = (string) $param;
			}
			else
			{
				// Escape single quotes and wrap in quotes
				$quotedParam = "'" . str_replace( "'", "''", $param ) . "'";
			}

			$debugSql = preg_replace( '/\?/', $quotedParam, $debugSql, 1 );
		}
		return $debugSql;
	}
	
	/**
	 * Binds a parameter to the specified variable name
	 *
	 * @param string|int $param
	 * @param mixed      $value
	 * @param int        $type
	 * @param ?int        $length
	 * @param mixed      $options
	 *
	 * @return self
	 */
	public function bindParam( string|int $param, mixed &$value, int $type = -1, ?int $length = null, mixed $options = null ): self
	{
		$this->stmt->bindParam( $param, $value, $type, $length, $options );

		return $this;
	}
	
	/**
	 * Binds a value to a parameter
	 *
	 * @param string|int $param
	 * @param mixed      $value
	 * @param int        $type
	 *
	 * @return self
	 */
	public function bindValue( string|int $param, mixed $value, int $type = -1 ): self
	{
		$this->stmt->bindValue( $param, $value, $type );

		return $this;
	}

	/**
	 * Bind a column to a PHP variable
	 *
	 * @param string|int $column
	 * @param mixed $param
	 * @param int|null $type
	 *
	 * @return self
	 */
	public function bindColumn( string|int $column, mixed &$param, ?int $type = null ): self
	{
		$this->stmt->bindColumn( $column, $param, $type );

		return $this;
	}
	
}

?>
