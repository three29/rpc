<?php

namespace RPC\Db\Adapter;


use RPC\Db\Adapter; 
use RPC\Db\Statement;
use PDO;


/**
 * MSSQL server Adapter implementation
 * 
 * @package Db
 */
class MSSQL extends Adapter
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
	 * @param mixed  $options
	 *
	 * @return static
	 */
	public function connect( string $username, string $password, mixed $options = null ): static
	{ 
		
		if( ! isset( $GLOBALS['dbconnection'] ) )
		{
			if( $this->_rpc_port )
			{
				$dsn = 'dblib:host=' . $this->_rpc_hostname . ':' . $this->_rpc_port .';dbname=' . $this->_rpc_database;
			}
			else
			{
				$dsn = 'dblib:host=' . $this->_rpc_hostname . ';dbname=' . $this->_rpc_database;
			}

			$GLOBALS['dbconnection'] = new \PDO( $dsn, $username, $password,  array(
		        PDO::ATTR_TIMEOUT => 3,
		        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		    ) );			
		}
		
		$this->setHandle( $GLOBALS['dbconnection'] );
		$this->getHandle()->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
		$this->getHandle()->setAttribute( \PDO::ATTR_CASE, \PDO::CASE_LOWER );
		
		return $this;
	}
	
	/**
	 * Overriding the default implementation as it seems to have a bug, at least
	 * with MSSQL
	 *
	 * @return int
	 */
	public function getLastId(): mixed
	{
		$sql = 'select scope_identity() as n';
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
	 * @param string|null $charset
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

		if( $sql != "select scope_identity() as n" )
		{
			if( getenv( 'LOG_QUERIES' ) === "true" )
			{
				$this->getHandle()->prepare( " insert into query_logger ( query, ip, created ) values ( ?, ?, ? ) " )->execute( array( $sql, \RPC\Util::get_client_source(), date( 'Y-m-d H:i:s' ) ) );
			}
		}

		$this->_rpc_affectedrows = $this->getHandle()->exec( $sql );

		if( $sql == "select scope_identity() as n" )
		{
			if( getenv( 'LOG_QUERIES' ) === "true" )
			{
				$this->getHandle()->prepare( " insert into query_logger ( query, ip, created ) values ( ?, ?, ? ) " )->execute( array( $sql, \RPC\Util::get_client_source(), date( 'Y-m-d H:i:s' ) ) );
			}
		}

		\RPC\Signal::getInstance()->dispatch(new \RPC\Events\QueryExecuted($sql, 'statement'));

		return $this->_rpc_affectedrows;
	}

	
}

?>
