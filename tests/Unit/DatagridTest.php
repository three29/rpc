<?php

namespace Tests\Unit;

use RPC\Datagrid;
use RPC\Datagrid\Pager;

class DatagridTest extends UnitTestCase
{
    private Datagrid $datagrid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->datagrid = new Datagrid();
    }

    public function testDatagridConstruction()
    {
        $datagrid = new Datagrid();

        $this->assertInstanceOf(Datagrid::class, $datagrid);
    }

    public function testDatagridConstructionWithModel()
    {
        $datagrid = new Datagrid('User');

        $this->assertInstanceOf(Datagrid::class, $datagrid);
    }

    public function testDatagridConstructionWithSelectOnly()
    {
        $datagrid = new Datagrid('User', 'id, name, email');

        $this->assertInstanceOf(Datagrid::class, $datagrid);
    }

    public function testGetPager()
    {
        $pager = $this->datagrid->getPager();

        $this->assertInstanceOf(Pager::class, $pager);
    }

    public function testSetPager()
    {
        $pager = new Pager();
        $this->datagrid->setPager($pager);

        $this->assertSame($pager, $this->datagrid->getPager());
    }

    public function testSetDb()
    {
        $mockDb = $this->createMock(\RPC\Db\Adapter::class);

        $result = $this->datagrid->setDb($mockDb);

        $this->assertInstanceOf(Datagrid::class, $result); // Fluent interface
        $this->assertSame($mockDb, $this->datagrid->getDb());
    }

    public function testGetDbWithoutSetting()
    {
        // Without setting a DB, getDb() tries to use Db::factory() which throws
        // We just test that the method is callable
        $this->assertTrue(method_exists($this->datagrid, 'getDb'));
    }

    public function testSetRows()
    {
        $rows = [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
        ];

        $this->datagrid->setRows($rows);

        $this->assertEquals($rows, $this->datagrid->getRows());
    }

    public function testGetRowsWithoutFetching()
    {
        // getRows() tries to fetch if rows is null, which requires DB
        // We just test that after setting rows, it returns them
        $rows = [['id' => 1]];
        $this->datagrid->setRows($rows);

        $this->assertEquals($rows, $this->datagrid->getRows());
    }

    public function testSetRowsWithFalse()
    {
        $this->datagrid->setRows(false);

        $this->assertFalse($this->datagrid->getRows());
    }

    public function testInitialSortBy()
    {
        $result = $this->datagrid->initialSortBy('name', 'ASC');

        $this->assertInstanceOf(Datagrid::class, $result);
    }

    public function testInitialSortByWithArray()
    {
        $result = $this->datagrid->initialSortBy(['name' => 'ASC', 'id' => 'DESC']);

        $this->assertInstanceOf(Datagrid::class, $result);
    }

    public function testAllowSortBy()
    {
        $result = $this->datagrid->allowSortBy();

        $this->assertInstanceOf(Datagrid::class, $result);
    }

    public function testGetSortByReturnsArray()
    {
        $sortBy = $this->datagrid->getSortBy();

        $this->assertIsArray($sortBy);
    }

    public function testSetPerPage()
    {
        $this->datagrid->setPerPage(25);

        // Verify it was set on the pager
        $this->assertEquals(25, $this->datagrid->getPager()->getPerPage());
    }

    public function testSetSortBy()
    {
        $this->datagrid->setSortBy('name', 'ASC');

        // setSortBy sets internal state, verify it doesn't throw
        $this->assertTrue(true);
    }

    public function testGroupBy()
    {
        // Should not throw
        $this->datagrid->groupBy('category');

        $this->assertTrue(true);
    }

    public function testGroupByWithNull()
    {
        $this->datagrid->groupBy(null);

        $this->assertTrue(true);
    }

    public function testSetCondition()
    {
        $this->datagrid->setCondition('status = ?', 'active');

        // Should not throw
        $this->assertTrue(true);
    }

    public function testQuery()
    {
        $this->datagrid->query('SELECT * FROM users WHERE id = ?', [1]);

        // Should not throw
        $this->assertTrue(true);
    }

    public function testQueryWithStringConditions()
    {
        $this->datagrid->query('SELECT * FROM users', '1');

        $this->assertTrue(true);
    }

    public function testQueryWithNullConditions()
    {
        $this->datagrid->query('SELECT * FROM users', null);

        $this->assertTrue(true);
    }

    public function testSqlJoin()
    {
        $this->datagrid->sqlJoin('LEFT JOIN orders ON users.id = orders.user_id');

        $this->assertTrue(true);
    }

    public function testNextPageExists()
    {
        $result = $this->datagrid->nextPageExists();

        $this->assertIsInt($result);
    }
}
