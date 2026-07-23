<?php

namespace RPC\Db\Table;

use ArrayAccess;

use RPC\Db;
use RPC\Db\Table\Row;

/**
 * Base class for all model classes, representing a table
 *
 * @package Db
 */
abstract class Adapter
{

	/**
	 * Represents a insert query
	 */
	const QUERY_INSERT = 'insert';

	/**
	 * Represents a update query
	 */
	const QUERY_UPDATE = 'update';

	/**
	 * Represents a delete query
	 */
	const QUERY_DELETE = 'delete';

	/**
	 * Table's database object
	 *
	 * @var \RPC\Db\Adapter|null
	 */
	protected $db = null;

	/**
	 * Table's name
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Force table's name
	 */
	public $force_table_name = '';

	public $model_name = '';

	/**
	 * Row object to be returned by load
	 *
	 * @var string
	 */
	protected $rowclass = '\RPC\Db\Table\Row';

	/**
	 * Table's primary key column's name
	 *
	 * @var string
	 */
	protected $pk = '';

	/**
	 * Array containing the fields
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Identity map for loaded rows from the table
	 *
	 * @var \RPC\Db\Table\Row\Map
	 */
	protected $map = null;

	abstract protected function loadFields(): void;

	abstract public function get(): array;

	abstract public function getAll(): array;

	abstract public function getBySql(): array|bool;

	/**
	 * Returns one row (the first in case there are more) which is returned by the query on the model's table. If no row is found, returns null
	 *
	 * @return \RPC\Db\Table\Row|false|null
	 */
	abstract public function find(): \RPC\Db\Table\Row|false|null;

	/**
	 * Returns all rows returned by the query on the model's table. If no row is found, returns empty array
	 *
	 * @return array
	 */
	abstract public function findAll(): array;

	/**
	 * Returns all rows returned by the custom query. If no row is found, returns false
	 *
	 * @return array|false
	 */
	abstract public function findBySql(): array|false;

	/**
	 * Removes the rows which have the $field = $value
	 *
	 * @param string $field
	 * @param mixed $value
	 *
	 * @return array|bool
	 */
	abstract public function deleteBy( string $field, mixed $value ): array|bool|null;

	/**
	 * Performs an insert given the supplied data
	 *
	 * @param \RPC\Db\Table\Row $row
	 *
	 * @return array|bool
	 */
	abstract protected function insertRow( \RPC\Db\Table\Row $row ): array|bool|null;

	/**
	 * Performs an update given the supplied data
	 *
	 * @param \RPC\Db\Table\Row $row
	 *
	 * @return array|bool
	 */
	abstract protected function updateRow( \RPC\Db\Table\Row $row ): array|bool|null;



	/**
	 * Locks a table
	 *
	 * @return void
	 */
	abstract public function lock(): void;

	/**
	 * Unlocks the table
	 *
	 * @return void
	 */
	abstract public function unlock(): void;

	/**
	 * Initializes the table based on two conventions:
	 * - object name will be: <table_name>Model
	 * - table primary key will be: <table_name>_id
	 */
	public function __construct( ?string $table_name = null, bool $ignore_fields = false )
	{
		if( $ignore_fields ||
		 	( count( explode( '\\', get_called_class() ) ) == 2 && ! $table_name ) )
		{
			$this->setDb( \RPC\Db::factory() );
		}
		else
		{
			if( $table_name )
			{
				$this->force_table_name = $table_name;

				if( ! $this->model_name )
				{
					$this->model_name = $table_name;
				}
			}

			$this->setDb( \RPC\Db::factory() );

			if( ! $this->force_table_name )
			{
				$classname = get_class( $this );
				$classname = explode( '\\', trim( $classname, '\\' ) );

				if( ! $this->model_name )
				{
					$this->model_name = end( $classname );
					$classrow = $this->rowclass . '\\' . $this->model_name;
				}
				else
				{
					$classrow = $this->rowclass . '\\' . end( $classname );
				}

				$classrow = str_replace( '_', '', $classrow );

				if( class_exists( $classrow ) )
				{
					$this->rowclass = $classrow;
				}
			}
			else
			{
				if( ! $this->model_name )
				{
					$this->model_name = $this->force_table_name;
				}
				$classrow = $this->rowclass . '\\' . $this->model_name;
				$classrow = str_replace( '_', '', $classrow );
				if( class_exists( $classrow ) )
				{
					$this->rowclass = $classrow;
				}
			}

			$this->setName( $this->getDb()->getPrefix() . strtolower( $this->model_name ) );

			$this->setPkField( 'id' );

			$this->loadFields();

			$this->setIdentityMap( new \RPC\Db\Table\Row\Map() );
		}
	}

	/**
	 * Sets the parent database connection
	 *
	 * @param \RPC\Db\Adapter $db
	 */
	protected function setDb( \RPC\Db\Adapter $db ): void
	{
		$this->db = $db;
	}

	/**
	 * Get the table's database connection
	 *
	 * @return \RPC\Db\Adapter
	 */
	public function getDb(): \RPC\Db\Adapter
	{
		return $this->db;
	}

	/**
	 * Sets the table name
	 *
	 * @param string $name
	 */
	public function setName( string $name ): void
	{
		$this->name = $name;
	}

	/**
	 * Get the table's name
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Sets an identity map for this table
	 *
	 * @param \RPC\Db\Table\Row\Map $map
	 */
	public function setIdentityMap( \RPC\Db\Table\Row\Map $map ): void
	{
		$this->map = $map;
	}

	/**
	 * Returns the table's identity map
	 *
	 * @return \RPC\Db\Table\Row\Map
	 */
	public function getIdentityMap(): \RPC\Db\Table\Row\Map
	{
		return $this->map;
	}


	/**
	 * Set the table's primary key
	 *
	 * @param string $pk
	 *
	 * @return \RPC\Db\Table\Adapter
	 */
	public function setPkField( string $pk ): self
	{
		$this->pk = $pk;
		return $this;
	}

	/**
	 * Get the table's primary key
	 *
	 * @return string
	 */
	public function getPkField(): string
	{
		return $this->pk;
	}

	/**
	 * Performs a raw query on the table.
	 *
	 * @return array|bool|null
	 */
	abstract static function query(): array|bool|null;

	/**
	 * Performs a raw query on the table.
	 *
	 * @return array|bool|null
	 */
	abstract static function execute(): array|bool|int|null;

	/**
	 * Create a new, empty row object
	 *
	 * @param array $data
	 *
	 * @return \RPC\Db\Table\Row
	 */
	public function create( array $data = array() ): \RPC\Db\Table\Row
	{
		/*
			We build an array of fields, filling the fields found in the
			array with the corresponding values and nulling the rest
		*/
		$tmp = array();
		foreach( $this->getFields() as $k => $field )
		{
			if( isset( $data[$field] ) &&
			    ! is_array( $data[$field] ) &&
			    ! is_object( $data[$field] ) )
			{
				$tmp[$field] = $data[$field];
			}
			else
			{
				$tmp[$field] = null;
			}

			$tmp[$this->getPkField()] = null;
		}

		return new $this->rowclass( $this, $tmp );
	}


	/**
	 * Get the table's fields
	 *
	 * @return array
	 *
	 * @todo Create a standard value object / structure for fields
	 */
	public function getFields(): array
	{
		return $this->fields;
	}


	/**
	 * Creates a prepared statement for insertion in the table of a given
	 * \RPC\Db\Table\Row
	 *
	 * @param \RPC\Db\Table\Row $row
	 *
	 * @return bool
	 */
	public function insert( \RPC\Db\Table\Row $row ): bool
	{
		$this->getDb()->beginTransaction();

		if( ! $this->onBeforeSave( $row, \RPC\Db::QUERY_INSERT ) )
		{
			$this->getDb()->rollback();
			return false;
		}

		if( ! $this->onBeforeInsert( $row ) )
		{
			$this->getDb()->rollback();
			return false;
		}

		$this->insertRow( $row );

		$pk = $this->getPkField();
		if( isset( $row->force_pk ) && $row->force_pk )
		{
			$row->force_pk = null;
		}
		else
		{
			$row->setPk( $this->getDb()->getLastId() );
		}

		if( ! $this->onAfterInsert( $row ) )
		{
			$this->getDb()->rollback();
			return false;
		}

		if( ! $this->onAfterSave( $row, \RPC\Db::QUERY_INSERT ) )
		{
			$this->getDb()->rollback();
			return false;
		}

		$this->getDb()->commit();
		$this->getIdentityMap()->add( $row );

		$this->afterCompleteInsert( $row );

		return true;
	}

	/**
	 * Hook called before executing an insert query. If it returns false the
	 * transaction will be rolled back
	 *
	 * @param \RPC\Db\Table\Row $row
	 *
	 * @return bool
	 */
	public function onBeforeInsert( \RPC\Db\Table\Row $row ): bool
	{
		//by default set created and modified dates
		$row->created( date( 'Y-m-d H:i:s' ) );
		$row->modified( date( 'Y-m-d H:i:s' ) );

		//set status to active by default
		if( ! $row->offsetGet( 'status' ) )
		{
			$row->status( 'active' );
		}
		elseif( $row->status() == 'deleted' &&
				! $row->offsetGet( 'deleted' ) )
		{
			$row->deleted( date( 'Y-m-d H:i:s' ) );
		}

		return true;
	}

	/**
	 * Hook called after executing an insert query. If it returns false the
	 * transaction will be rolled back
	 *
	 * @param \RPC\Db\Table\Row $row
	 *
	 * @return bool
	 */
	public function onAfterInsert( \RPC\Db\Table\Row $row ): bool
	{
		return true;
	}

	/**
	 * Hook called after complete PDO Transaction
	 *
	 * @return bool
	 */
	public function afterCompleteInsert( \RPC\Db\Table\Row $row ): bool {
		return true;
	}

	/**
	 * Creates a prepared statement for update in the table the given row
	 * identified by it's primary key
	 *
	 * @param \RPC\Db\Table\Row $row
	 *
	 * @return bool
	 */
	public function update( \RPC\Db\Table\Row $row ): bool
	{
		$this->getDb()->beginTransaction();

		if( ! $this->onBeforeSave( $row, \RPC\Db::QUERY_UPDATE ) )
		{
			$this->getDb()->rollback();
			return false;
		}

		if( ! $this->onBeforeUpdate( $row ) )
		{
			$this->getDb()->rollback();
			return false;
		}

		try
		{
			$this->updateRow( $row );
		}
		catch( \Exception $e )
		{
			$this->getDb()->rollback();
			return false;
		}

		if( ! $this->onAfterUpdate( $row ) )
		{
			$this->getDb()->rollback();
			return false;
		}

		if( ! $this->onAfterSave( $row, \RPC\Db::QUERY_UPDATE ) )
		{
			$this->getDb()->rollback();
			return false;
		}

		$this->getDb()->commit();

		$this->afterCompleteUpdate( $row );

		return true;
	}

	/**
	 * Hook called before executing an update query. If it returns false the
	 * transaction will be rolled back
	 *
	 * @param \RPC\Db\Table\Row $row
	 *
	 * @return bool
	 */
	public function onBeforeUpdate( \RPC\Db\Table\Row $row ): bool
	{
		//set modified date by default
		$row->modified( date( 'Y-m-d H:i:s' ) );

		if( $row->offsetGet( 'status' ) &&
			$row->status() == 'deleted' &&
			! $row->offsetGet( 'deleted' ) )
		{
			$row->deleted( date( 'Y-m-d H:i:s' ) );
		}

		return true;
	}

	/**
	 * Hook called after PDO Transaction
	 *
	 * @param \RPC\Db\Table\Row $row
	 *
	 * @return bool
	 */
	public function afterCompleteUpdate( \RPC\Db\Table\Row $row ): bool {
		return true;
	}

	/**
	 * Hook called after executing an update query. If it returns false the
	 * transaction will be rolled back
	 *
	 * @param \RPC\Db\Table\Row $row
	 *
	 * @return bool
	 */
	public function onAfterUpdate( \RPC\Db\Table\Row $row ): bool
	{
		return true;
	}

	public function onBeforeSave( \RPC\Db\Table\Row $row, string $op ): bool
	{
		return true;
	}

	public function onAfterSave( \RPC\Db\Table\Row $row, string $op ): bool
	{
		return true;
	}

	/**
	 * Delets the given row
	 *
	 * @param \RPC\Db\Table\Row $row
	 *
	 * @return bool
	 */
	public function delete( \RPC\Db\Table\Row $row ): bool
	{
		$this->getDb()->beginTransaction();

		if( ! $this->onBeforeDelete( $row ) )
		{
			$this->getDb()->rollback();
			return false;
		}

		if( ! (bool) $this->deleteBy( $this->getPkField(), $row->getPk() ) )
		{
			$this->getDb()->rollback();
			return false;
		}

		if( ! $this->onAfterDelete( $row ) )
		{
			$this->getDb()->rollback();
			return false;
		}

		$this->getDb()->commit();
		$this->getIdentityMap()->remove( $row );

		return true;
	}

	/**
	 * Hook called before executing a delete query. If it returns false the
	 * transaction will be rolled back
	 *
	 * @param \RPC\Db\Table\Row $row
	 *
	 * @return bool
	 */
	public function onBeforeDelete( \RPC\Db\Table\Row $row ): bool
	{
		return true;
	}

	/**
	 * Hook called after executing a delete query. If it returns false the
	 * transaction will be rolled back
	 *
	 * @param \RPC\Db\Table\Row $row
	 *
	 * @return bool
	 */
	public function onAfterDelete( \RPC\Db\Table\Row $row ): bool
	{
		return true;
	}

	public function lastQuery( bool $show_all = false ): mixed
	{
		return $this->getDb()->getQueries( $show_all );
	}

}

?>
