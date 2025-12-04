<?php

namespace Tests\Unit\Db\Table\Row;

use RPC\Db\Table\Row;
use RPC\Db\Table\Row\Map;
use RPC\Db\Table\Adapter;
use Tests\Unit\UnitTestCase;

class MapTest extends UnitTestCase
{
    private $map;
    private $mockTable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->map = new Map();

        // Create mock Table adapter
        $this->mockTable = $this->createMock(Adapter::class);
        $this->mockTable->method('getPkField')->willReturn('id');
        $this->mockTable->method('getFields')->willReturn(['id', 'name']);
    }

    public function testMapConstruction()
    {
        $this->assertInstanceOf(Map::class, $this->map);
    }

    public function testAddRow()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test']);

        $this->map->add($row);

        $retrievedRow = $this->map->get(1);
        $this->assertSame($row, $retrievedRow);
    }

    public function testAddMultipleRows()
    {
        $row1 = new Row($this->mockTable, ['id' => 1, 'name' => 'Test 1']);
        $row2 = new Row($this->mockTable, ['id' => 2, 'name' => 'Test 2']);
        $row3 = new Row($this->mockTable, ['id' => 3, 'name' => 'Test 3']);

        $this->map->add($row1);
        $this->map->add($row2);
        $this->map->add($row3);

        $this->assertSame($row1, $this->map->get(1));
        $this->assertSame($row2, $this->map->get(2));
        $this->assertSame($row3, $this->map->get(3));
    }

    public function testAddDuplicateRowDoesNotOverwrite()
    {
        $row1 = new Row($this->mockTable, ['id' => 1, 'name' => 'First']);
        $row2 = new Row($this->mockTable, ['id' => 1, 'name' => 'Second']);

        $this->map->add($row1);
        $this->map->add($row2); // Should not overwrite

        $retrievedRow = $this->map->get(1);
        $this->assertSame($row1, $retrievedRow);
        $this->assertEquals('First', $retrievedRow['name']);
    }

    public function testGetNonexistentRow()
    {
        $result = $this->map->get(999);

        $this->assertNull($result);
    }

    public function testRemoveRow()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test']);

        $this->map->add($row);
        $this->assertNotNull($this->map->get(1));

        $this->map->remove($row);
        $this->assertNull($this->map->get(1));
    }

    public function testRemoveMultipleRows()
    {
        $row1 = new Row($this->mockTable, ['id' => 1, 'name' => 'Test 1']);
        $row2 = new Row($this->mockTable, ['id' => 2, 'name' => 'Test 2']);

        $this->map->add($row1);
        $this->map->add($row2);

        $this->map->remove($row1);

        $this->assertNull($this->map->get(1));
        $this->assertNotNull($this->map->get(2));
    }

    public function testIdentityMapPattern()
    {
        // Identity map should always return the same instance
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Test']);

        $this->map->add($row);

        $retrieved1 = $this->map->get(1);
        $retrieved2 = $this->map->get(1);

        $this->assertSame($retrieved1, $retrieved2);
        $this->assertSame($row, $retrieved1);
    }

    public function testMapWithModifiedRow()
    {
        $row = new Row($this->mockTable, ['id' => 1, 'name' => 'Original']);

        $this->map->add($row);

        // Modify the row
        $row['name'] = 'Modified';

        // Get from map should return the same modified instance
        $retrievedRow = $this->map->get(1);
        $this->assertEquals('Modified', $retrievedRow['name']);
    }
}
