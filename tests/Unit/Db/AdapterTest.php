<?php

namespace Tests\Unit\Db;

use RPC\Db\Adapter\MySQL;
use Tests\Unit\UnitTestCase;

class AdapterTest extends UnitTestCase
{
    public function testMySQLAdapterConstruction()
    {
        $adapter = new MySQL('localhost', 'test_db', null, 3306);

        $this->assertInstanceOf(MySQL::class, $adapter);
    }

    public function testMySQLAdapterDefaults()
    {
        $adapter = new MySQL();

        $this->assertInstanceOf(MySQL::class, $adapter);
    }

    public function testConnectReturnsAdapter()
    {
        $adapter = new MySQL('localhost', 'test_db');
        $result = $adapter->connect('user', 'pass');

        $this->assertSame($adapter, $result);
    }

    public function testSetPrefix()
    {
        $adapter = new MySQL('localhost', 'test_db');
        $adapter->setPrefix('test_');

        $this->assertEquals('test_', $adapter->getPrefix());
    }

    public function testGetPrefix()
    {
        $adapter = new MySQL('localhost', 'test_db');

        $this->assertEquals('', $adapter->getPrefix());
    }

    public function testSetFetchMode()
    {
        $adapter = new MySQL('localhost', 'test_db');
        $adapter->setFetchMode(\RPC\Db::FETCH_NUM);

        $this->assertEquals(\RPC\Db::FETCH_NUM, $adapter->getFetchMode());
    }

    public function testGetFetchMode()
    {
        $adapter = new MySQL('localhost', 'test_db');

        // Default should be FETCH_ASSOC
        $this->assertEquals(\RPC\Db::FETCH_ASSOC, $adapter->getFetchMode());
    }

    public function testGetAffectedRows()
    {
        $adapter = new MySQL('localhost', 'test_db');

        // Before any query, affected rows should be 0
        $this->assertEquals(0, $adapter->getAffectedRows());
    }

    public function testAdapterImplementsAbstractMethods()
    {
        $adapter = new MySQL('localhost', 'test_db');

        // Verify abstract methods are implemented
        $this->assertTrue(method_exists($adapter, 'connect'));
        $this->assertTrue(method_exists($adapter, 'getLastId'));
        $this->assertTrue(method_exists($adapter, 'setCharset'));
        $this->assertTrue(method_exists($adapter, 'prepare'));
    }
}
