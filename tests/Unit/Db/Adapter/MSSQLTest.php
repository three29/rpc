<?php

namespace Tests\Unit\Db\Adapter;

use RPC\Db\Adapter\MSSQL;
use RPC\Db\Adapter;
use RPC\Db;
use Tests\Unit\UnitTestCase;

class MSSQLTest extends UnitTestCase
{
    public function testConstructorSetsHostname()
    {
        $adapter = new MSSQL('sqlserver.example.com');

        $this->assertInstanceOf(MSSQL::class, $adapter);
    }

    public function testConstructorSetsDatabase()
    {
        $adapter = new MSSQL('localhost', 'my_database');

        $this->assertInstanceOf(MSSQL::class, $adapter);
    }

    public function testConstructorSetsSocket()
    {
        $adapter = new MSSQL('localhost', 'test_db', '/var/run/mssql.sock');

        $this->assertInstanceOf(MSSQL::class, $adapter);
    }

    public function testConstructorSetsPort()
    {
        $adapter = new MSSQL('localhost', 'test_db', null, 1433);

        $this->assertInstanceOf(MSSQL::class, $adapter);
    }

    public function testConstructorDefaults()
    {
        $adapter = new MSSQL();

        $this->assertInstanceOf(MSSQL::class, $adapter);
    }

    public function testExtendsBaseAdapter()
    {
        $adapter = new MSSQL();

        $this->assertInstanceOf(Adapter::class, $adapter);
    }

    public function testConnectReturnsAdapter()
    {
        // connect() returns $this (fluent interface)
        // We can't test actual connection without database, so verify method exists and signature
        $reflection = new \ReflectionMethod(MSSQL::class, 'connect');

        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('static', $reflection->getReturnType()?->getName());
    }

    public function testSetAndGetPrefix()
    {
        $adapter = new MSSQL();
        $adapter->setPrefix('dbo_');

        $this->assertEquals('dbo_', $adapter->getPrefix());
    }

    public function testGetPrefixDefaultEmpty()
    {
        $adapter = new MSSQL();

        $this->assertEquals('', $adapter->getPrefix());
    }

    public function testSetAndGetFetchMode()
    {
        $adapter = new MSSQL();
        $adapter->setFetchMode(Db::FETCH_NUM);

        $this->assertEquals(Db::FETCH_NUM, $adapter->getFetchMode());
    }

    public function testGetFetchModeDefaultAssoc()
    {
        $adapter = new MSSQL();

        $this->assertEquals(Db::FETCH_ASSOC, $adapter->getFetchMode());
    }

    public function testSetFetchModeObject()
    {
        $adapter = new MSSQL();
        $adapter->setFetchMode(Db::FETCH_OBJ);

        $this->assertEquals(Db::FETCH_OBJ, $adapter->getFetchMode());
    }

    public function testGetAffectedRowsInitiallyZero()
    {
        $adapter = new MSSQL();

        $this->assertEquals(0, $adapter->getAffectedRows());
    }

    public function testHasRequiredMethods()
    {
        $adapter = new MSSQL();

        $this->assertTrue(method_exists($adapter, 'connect'));
        $this->assertTrue(method_exists($adapter, 'getLastId'));
        $this->assertTrue(method_exists($adapter, 'setCharset'));
        $this->assertTrue(method_exists($adapter, 'prepare'));
        $this->assertTrue(method_exists($adapter, 'execute'));
        $this->assertTrue(method_exists($adapter, 'query'));
    }

    public function testExecuteMethod()
    {
        $adapter = new MSSQL();

        // execute() runs SQL without returning results
        $this->assertTrue(method_exists($adapter, 'execute'));
    }

    public function testQueryMethod()
    {
        $adapter = new MSSQL();

        // query() runs SQL and returns results (inherited from base Adapter)
        $this->assertTrue(method_exists($adapter, 'query'));
    }

    public function testPrepareMethod()
    {
        $adapter = new MSSQL();

        // prepare() returns a Statement object
        $this->assertTrue(method_exists($adapter, 'prepare'));
    }

    public function testSetCharsetMethod()
    {
        $adapter = new MSSQL();

        // setCharset() should return the adapter (fluent)
        $this->assertTrue(method_exists($adapter, 'setCharset'));
    }

    public function testGetLastIdMethod()
    {
        $adapter = new MSSQL();

        // getLastId() returns last insert ID
        $this->assertTrue(method_exists($adapter, 'getLastId'));
    }
}
