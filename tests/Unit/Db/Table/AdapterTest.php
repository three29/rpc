<?php

namespace Tests\Unit\Db\Table;

use RPC\Db\Table\Adapter;
use RPC\Db\Table\Row;
use RPC\Db\Table\Row\Map;
use RPC\Db\Adapter as DbAdapter;
use Tests\Unit\UnitTestCase;

class AdapterTest extends UnitTestCase
{
    private $mockDb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockDb = $this->createMock(DbAdapter::class);
    }

    public function testAdapterConstants()
    {
        // Use reflection to check constants since Adapter is abstract
        $reflection = new \ReflectionClass(Adapter::class);

        $this->assertEquals('insert', $reflection->getConstant('QUERY_INSERT'));
        $this->assertEquals('update', $reflection->getConstant('QUERY_UPDATE'));
        $this->assertEquals('delete', $reflection->getConstant('QUERY_DELETE'));
    }

    public function testAdapterAbstractMethods()
    {
        $reflection = new \ReflectionClass(Adapter::class);

        // Verify all abstract methods exist
        $this->assertTrue($reflection->hasMethod('loadFields'));
        $this->assertTrue($reflection->hasMethod('get'));
        $this->assertTrue($reflection->hasMethod('getAll'));
        $this->assertTrue($reflection->hasMethod('getBySql'));
        $this->assertTrue($reflection->hasMethod('find'));
        $this->assertTrue($reflection->hasMethod('findAll'));
        $this->assertTrue($reflection->hasMethod('findBySql'));
        $this->assertTrue($reflection->hasMethod('deleteBy'));
        $this->assertTrue($reflection->hasMethod('insertRow'));
        $this->assertTrue($reflection->hasMethod('updateRow'));
        $this->assertTrue($reflection->hasMethod('lock'));
        $this->assertTrue($reflection->hasMethod('unlock'));
    }

    public function testAdapterHookMethods()
    {
        $reflection = new \ReflectionClass(Adapter::class);

        // Verify all hook methods exist
        $this->assertTrue($reflection->hasMethod('onBeforeInsert'));
        $this->assertTrue($reflection->hasMethod('onAfterInsert'));
        $this->assertTrue($reflection->hasMethod('onBeforeUpdate'));
        $this->assertTrue($reflection->hasMethod('onAfterUpdate'));
        $this->assertTrue($reflection->hasMethod('onBeforeSave'));
        $this->assertTrue($reflection->hasMethod('onAfterSave'));
        $this->assertTrue($reflection->hasMethod('onBeforeDelete'));
        $this->assertTrue($reflection->hasMethod('onAfterDelete'));
    }

    public function testConcreteAdapterImplementation()
    {
        // Create a concrete implementation for testing
        $adapter = new class($this->mockDb) extends Adapter {
            private $testDb;

            public function __construct($db) {
                $this->testDb = $db;
                $this->name = 'test_table';
                $this->pk = 'id';
                $this->fields = ['id', 'name', 'email'];
                $this->map = new Map();
            }

            public function getDb(): \RPC\Db\Adapter {
                return $this->testDb;
            }

            protected function loadFields(): void {
                $this->fields = ['id', 'name', 'email'];
            }

            public function get(): array { return []; }
            public function getAll(): array { return []; }
            public function getBySql(): array|false { return []; }
            public function find(): ?\RPC\Db\Table\Row { return null; }
            public function findAll(): array { return []; }
            public function findBySql(): array|false { return []; }
            public function deleteBy(string $field, mixed $value): array|bool { return true; }
            protected function insertRow(\RPC\Db\Table\Row $row): array|bool { return true; }
            protected function updateRow(\RPC\Db\Table\Row $row): array|bool { return true; }
            public function lock(): void {}
            public function unlock(): void {}
        };

        $this->assertInstanceOf(Adapter::class, $adapter);
        $this->assertEquals('test_table', $adapter->getName());
        $this->assertEquals('id', $adapter->getPkField());
        $this->assertEquals(['id', 'name', 'email'], $adapter->getFields());
    }

    public function testSetAndGetName()
    {
        $adapter = $this->createMinimalAdapter();

        $adapter->setName('users');
        $this->assertEquals('users', $adapter->getName());

        $adapter->setName('products');
        $this->assertEquals('products', $adapter->getName());
    }

    public function testSetAndGetPkField()
    {
        $adapter = $this->createMinimalAdapter();

        $result = $adapter->setPkField('user_id');
        $this->assertInstanceOf(Adapter::class, $result); // Fluent interface
        $this->assertEquals('user_id', $adapter->getPkField());
    }

    public function testSetAndGetIdentityMap()
    {
        $adapter = $this->createMinimalAdapter();

        $map = new Map();
        $adapter->setIdentityMap($map);

        $this->assertSame($map, $adapter->getIdentityMap());
    }

    public function testCreateEmptyRow()
    {
        $adapter = $this->createMinimalAdapter();

        $row = $adapter->create();

        $this->assertInstanceOf(Row::class, $row);
        $this->assertNull($row->getPk());
    }

    public function testCreateRowWithData()
    {
        $adapter = $this->createMinimalAdapter();

        $row = $adapter->create(['name' => 'John', 'email' => 'john@example.com']);

        $this->assertInstanceOf(Row::class, $row);
        $this->assertEquals('John', $row['name']);
        $this->assertEquals('john@example.com', $row['email']);
    }

    public function testCreateRowFiltersNonTableFields()
    {
        $adapter = $this->createMinimalAdapter();

        $row = $adapter->create([
            'name' => 'John',
            'email' => 'john@example.com',
            'invalid_field' => 'should be null'
        ]);

        $this->assertEquals('John', $row['name']);
        $this->assertNull($row['invalid_field']);
    }

    public function testCreateRowThrowsExceptionForNonArrayData()
    {
        $this->expectException(\TypeError::class);

        $adapter = $this->createMinimalAdapter();
        $adapter->create('not an array');
    }

    public function testOnBeforeInsertSetsTimestamps()
    {
        $adapter = $this->createMinimalAdapter(['id', 'name', 'created', 'modified', 'status']);

        $row = $adapter->create();
        $adapter->onBeforeInsert($row);

        $this->assertNotNull($row['created']);
        $this->assertNotNull($row['modified']);
    }

    public function testOnBeforeInsertSetsDefaultStatus()
    {
        $adapter = $this->createMinimalAdapter(['id', 'name', 'status']);

        $row = $adapter->create();
        $adapter->onBeforeInsert($row);

        $this->assertEquals('active', $row['status']);
    }

    public function testOnBeforeUpdateSetsModifiedTimestamp()
    {
        $adapter = $this->createMinimalAdapter(['id', 'name', 'modified']);

        $row = $adapter->create(['id' => 1, 'name' => 'Test']);
        $adapter->onBeforeUpdate($row);

        $this->assertNotNull($row['modified']);
    }

    public function testHookMethodsReturnTrue()
    {
        $adapter = $this->createMinimalAdapter();
        $row = $adapter->create();

        $this->assertTrue($adapter->onAfterInsert($row));
        $this->assertTrue($adapter->onBeforeUpdate($row));
        $this->assertTrue($adapter->onAfterUpdate($row));
        $this->assertTrue($adapter->onBeforeSave($row, Adapter::QUERY_INSERT));
        $this->assertTrue($adapter->onAfterSave($row, Adapter::QUERY_UPDATE));
        $this->assertTrue($adapter->onBeforeDelete($row));
        $this->assertTrue($adapter->onAfterDelete($row));
    }

    /**
     * Helper method to create a minimal concrete adapter for testing
     */
    private function createMinimalAdapter(array $fields = ['id', 'name', 'email'])
    {
        $mockDb = $this->mockDb;

        return new class($mockDb, $fields) extends Adapter {
            private $testDb;
            private $testFields;

            public function __construct($db, $fields) {
                $this->testDb = $db;
                $this->testFields = $fields;
                $this->name = 'test_table';
                $this->pk = 'id';
                $this->fields = $fields;
                $this->map = new Map();
            }

            public function getDb(): \RPC\Db\Adapter {
                return $this->testDb;
            }

            protected function loadFields(): void {
                $this->fields = $this->testFields;
            }

            public function get(): array { return []; }
            public function getAll(): array { return []; }
            public function getBySql(): array|false { return []; }
            public function find(): ?\RPC\Db\Table\Row { return null; }
            public function findAll(): array { return []; }
            public function findBySql(): array|false { return []; }
            public function deleteBy(string $field, mixed $value): array|bool { return true; }
            protected function insertRow(\RPC\Db\Table\Row $row): array|bool { return true; }
            protected function updateRow(\RPC\Db\Table\Row $row): array|bool { return true; }
            public function lock(): void {}
            public function unlock(): void {}
        };
    }
}
