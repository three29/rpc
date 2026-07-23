<?php

namespace Tests\Unit\Db\Table;

use RPC\Db\Table\Row;
use RPC\Db\Table\Adapter;
use RPC\Db\Adapter as DbAdapter;
use Tests\Unit\UnitTestCase;

class RowTest extends UnitTestCase
{
    private $mockTable;
    private $mockDb;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock DB adapter
        $this->mockDb = $this->createMock(DbAdapter::class);

        // Create mock Table adapter
        $this->mockTable = $this->createMock(Adapter::class);
        $this->mockTable->method('getDb')->willReturn($this->mockDb);
        $this->mockTable->method('getPkField')->willReturn('id');
        $this->mockTable->method('getFields')->willReturn(['id', 'name', 'email', 'created']);
    }

    public function testRowConstruction()
    {
        $data = ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com'];
        $row = new Row($this->mockTable, $data);

        $this->assertInstanceOf(Row::class, $row);
        $this->assertEquals(1, $row->getPk());
        $this->assertEquals('Test', $row['name']);
    }

    public function testArrayAccessGet()
    {
        $data = ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com'];
        $row = new Row($this->mockTable, $data);

        $this->assertEquals(1, $row['id']);
        $this->assertEquals('Test', $row['name']);
        $this->assertEquals('test@example.com', $row['email']);
    }

    public function testArrayAccessSet()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Old Name']);

        $row['name'] = 'New Name';
        $this->assertEquals('New Name', $row['name']);
    }

    public function testArrayAccessExists()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test']);

        $this->assertTrue(isset($row['name']));
        $this->assertFalse(isset($row['nonexistent']));
    }

    public function testArrayAccessUnsetThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot remove a field from the row');

        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test']);
        unset($row['name']);
    }

    public function testSetPrimaryKeyThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The primary key can only be changed using the setPk method');

        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test']);
        $row['id'] = 2;
    }

    public function testSetPkMethod()
    {
        $row = new Row($this->mockTable, ['name' => 'Test']);

        $row->setPk(5);
        $this->assertEquals(5, $row->getPk());
    }

    public function testSetPkWithEmptyValueThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Primary key cannot be empty');

        $row = new Row($this->mockTable, ['name' => 'Test']);
        $row->setPk('');
    }

    public function testIsDirty()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test']);

        $this->assertFalse($row->isDirty());

        $row['name'] = 'New Name';
        $this->assertTrue($row->isDirty());
    }

    public function testGetChangedFields()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com']);

        $row['name'] = 'New Name';
        $row['email'] = 'new@example.com';

        $changedFields = $row->getChangedFields();
        $this->assertArrayHasKey('name', $changedFields);
        $this->assertArrayHasKey('email', $changedFields);
    }

    public function testGetCleanArray()
    {
        $originalData = ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com'];
        $row = new Row($this->mockTable, $originalData);

        $row['name'] = 'New Name';

        $cleanArray = $row->getCleanArray();
        $this->assertEquals('Test', $cleanArray['name']);
        $this->assertEquals('New Name', $row['name']);
    }

    public function testRevert()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test']);

        $row['name'] = 'New Name';
        $this->assertEquals('New Name', $row['name']);

        $row->revert();
        $this->assertEquals('Test', $row['name']);
    }

    public function testPopulate()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => '', 'email' => '']);

        $row->populate(['name' => 'John', 'email' => 'john@example.com']);

        $this->assertEquals('John', $row['name']);
        $this->assertEquals('john@example.com', $row['email']);
    }

    public function testPopulateWithSkipOption()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Original', 'email' => 'original@example.com']);

        $row->populate(
            ['name' => 'New', 'email' => 'new@example.com'],
            ['skip' => 'email']
        );

        $this->assertEquals('New', $row['name']);
        $this->assertEquals('original@example.com', $row['email']); // Should not change
    }

    public function testPopulateWithSpecificFields()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Original', 'email' => 'original@example.com']);

        $row->populate(
            ['name' => 'New', 'email' => 'new@example.com'],
            ['name'] // Only update name field
        );

        $this->assertEquals('New', $row['name']);
        $this->assertEquals('original@example.com', $row['email']); // Should not change
    }

    public function testExtraFields()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test']);

        // Set an extra field not in table schema
        $row->populate(['custom_field' => 'custom_value']);

        $extraFields = $row->getExtraFields();
        $this->assertArrayHasKey('custom_field', $extraFields);
        $this->assertEquals('custom_value', $row['custom_field']);
    }

    public function testErrorHandling()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test']);

        $this->assertEquals(0, $row->hasErrors());

        $row->setError('name', 'Name is invalid');
        $this->assertEquals(1, $row->hasErrors());
        $this->assertEquals('Name is invalid', $row->getError('name'));

        $errors = $row->getErrors();
        $this->assertArrayHasKey('name', $errors);
    }

    public function testSetMultipleErrors()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test']);

        $row->setErrors([
            'name' => 'Name is required',
            'email' => 'Email is invalid'
        ]);

        $this->assertEquals(2, $row->hasErrors());
        $this->assertEquals('Name is required', $row->getError('name'));
        $this->assertEquals('Email is invalid', $row->getError('email'));
    }

    public function testGetData()
    {
        $originalData = ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com', 'created' => null];
        $row = new Row($this->mockTable, $originalData);

        $data = $row->getData();

        $this->assertIsArray($data);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Test', $data['name']);
        $this->assertEquals('test@example.com', $data['email']);
    }

    public function testGetDataWithExtraFields()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test', 'email' => null, 'created' => null]);
        $row->populate(['custom_field' => 'custom_value']);

        $data = $row->getData();

        $this->assertArrayHasKey('custom_field', $data);
        $this->assertEquals('custom_value', $data['custom_field']);
    }

    public function testMagicCallGetter()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test']);

        $this->assertEquals('Test', $row->name());
    }

    public function testMagicCallSetter()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test']);

        $row->name('New Name');
        $this->assertEquals('New Name', $row['name']);
    }

    public function testMagicCallNonexistentField()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Field nonexistent doesn't exist on the row object");

        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test']);
        $row->nonexistent();
    }

    public function testGetTable()
    {
        $row = new Row($this->mockTable, ['id' => 1]);

        $this->assertSame($this->mockTable, $row->getTable());
    }

    public function testGetDb()
    {
        $row = new Row($this->mockTable, ['id' => 1]);

        $this->assertSame($this->mockDb, $row->getDb());
    }

    public function testGetFields()
    {
        $row = new Row($this->mockTable, ['id' => 1]);

        $fields = $row->getFields();
        $this->assertEquals(['id', 'name', 'email', 'created'], $fields);
    }

    public function testClone()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test', 'email' => 'test@example.com', 'created' => '2024-01-01']);

        $cloned = clone $row;

        $this->assertNull($cloned->getPk());
        $this->assertNull($cloned['created']);
        $this->assertEquals('Test', $cloned['name']);
        $this->assertEquals(0, $cloned->hasErrors());
    }
}
