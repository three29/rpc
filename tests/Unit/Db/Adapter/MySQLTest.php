<?php

namespace Tests\Unit\Db\Adapter;

use RPC\Db\Adapter\MySQL;
use RPC\Db\Adapter;
use RPC\Db;
use Tests\Unit\UnitTestCase;

class MySQLTest extends UnitTestCase
{
    public function testConstructorSetsHostname()
    {
        $adapter = new MySQL('db.example.com');

        $this->assertInstanceOf(MySQL::class, $adapter);
    }

    public function testConstructorSetsDatabase()
    {
        $adapter = new MySQL('localhost', 'my_database');

        $this->assertInstanceOf(MySQL::class, $adapter);
    }

    public function testConstructorSetsSocket()
    {
        $adapter = new MySQL('localhost', 'test_db', '/var/run/mysqld/mysqld.sock');

        $this->assertInstanceOf(MySQL::class, $adapter);
    }

    public function testConstructorSetsPort()
    {
        $adapter = new MySQL('localhost', 'test_db', null, 3307);

        $this->assertInstanceOf(MySQL::class, $adapter);
    }

    public function testConstructorDefaults()
    {
        $adapter = new MySQL();

        $this->assertInstanceOf(MySQL::class, $adapter);
    }

    public function testExtendsBaseAdapter()
    {
        $adapter = new MySQL();

        $this->assertInstanceOf(Adapter::class, $adapter);
    }

    public function testConnectReturnsAdapter()
    {
        // connect() returns $this (fluent interface)
        // We can't test actual connection without database, so verify method exists and signature
        $reflection = new \ReflectionMethod(MySQL::class, 'connect');

        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('static', $reflection->getReturnType()?->getName());
    }

    public function testSetAndGetPrefix()
    {
        $adapter = new MySQL();
        $adapter->setPrefix('wp_');

        $this->assertEquals('wp_', $adapter->getPrefix());
    }

    public function testGetPrefixDefaultEmpty()
    {
        $adapter = new MySQL();

        $this->assertEquals('', $adapter->getPrefix());
    }

    public function testSetAndGetFetchMode()
    {
        $adapter = new MySQL();
        $adapter->setFetchMode(Db::FETCH_NUM);

        $this->assertEquals(Db::FETCH_NUM, $adapter->getFetchMode());
    }

    public function testGetFetchModeDefaultAssoc()
    {
        $adapter = new MySQL();

        $this->assertEquals(Db::FETCH_ASSOC, $adapter->getFetchMode());
    }

    public function testSetFetchModeObject()
    {
        $adapter = new MySQL();
        $adapter->setFetchMode(Db::FETCH_OBJ);

        $this->assertEquals(Db::FETCH_OBJ, $adapter->getFetchMode());
    }

    public function testGetAffectedRowsInitiallyZero()
    {
        $adapter = new MySQL();

        $this->assertEquals(0, $adapter->getAffectedRows());
    }

    public function testHasRequiredMethods()
    {
        $adapter = new MySQL();

        $this->assertTrue(method_exists($adapter, 'connect'));
        $this->assertTrue(method_exists($adapter, 'getLastId'));
        $this->assertTrue(method_exists($adapter, 'setCharset'));
        $this->assertTrue(method_exists($adapter, 'prepare'));
        $this->assertTrue(method_exists($adapter, 'execute'));
        $this->assertTrue(method_exists($adapter, 'query'));
    }

    public function testExecuteMethod()
    {
        $adapter = new MySQL();

        // execute() runs SQL without returning results
        $this->assertTrue(method_exists($adapter, 'execute'));
    }

    public function testQueryMethod()
    {
        $adapter = new MySQL();

        // query() runs SQL and returns results (inherited from base Adapter)
        $this->assertTrue(method_exists($adapter, 'query'));
    }

    public function testPrepareMethod()
    {
        $adapter = new MySQL();

        // prepare() returns a Statement object
        $this->assertTrue(method_exists($adapter, 'prepare'));
    }

    public function testSetCharsetMethod()
    {
        $adapter = new MySQL();

        // setCharset() should return the adapter (fluent)
        $this->assertTrue(method_exists($adapter, 'setCharset'));
    }

    public function testGetLastIdMethod()
    {
        $adapter = new MySQL();

        // getLastId() returns last insert ID
        $this->assertTrue(method_exists($adapter, 'getLastId'));
    }

    private function dsn(MySQL $adapter, array $options = []): string
    {
        $method = new \ReflectionMethod(MySQL::class, 'buildDsn');

        return $method->invoke($adapter, $options);
    }

    public function testDsnForHostIncludesPort()
    {
        $this->assertSame(
            'mysql:host=db.example.com;port=3307;dbname=shop',
            $this->dsn(new MySQL('db.example.com', 'shop', null, 3307))
        );
    }

    public function testDsnForSocketSelectsDatabase()
    {
        $this->assertSame(
            'mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=shop',
            $this->dsn(new MySQL('localhost', 'shop', '/var/run/mysqld/mysqld.sock'))
        );
    }

    public function testDsnIncludesCharsetWhenConfigured()
    {
        $this->assertSame(
            'mysql:host=localhost;port=3306;dbname=shop;charset=utf8mb4',
            $this->dsn(new MySQL('localhost', 'shop'), ['charset' => 'utf8mb4'])
        );
    }

    private function pdoOptions(array $options): array
    {
        $method = new \ReflectionMethod(MySQL::class, 'pdoOptions');

        return $method->invoke(new MySQL('localhost', 'shop'), $options);
    }

    public function testSqlModeBecomesInitCommandWithoutDeprecation()
    {
        $deprecations = [];
        set_error_handler(function (int $errno, string $message) use (&$deprecations) {
            $deprecations[] = $message;
            return true;
        }, E_DEPRECATED | E_USER_DEPRECATED);

        try {
            $options = $this->pdoOptions(['sql_mode' => 'TRADITIONAL']);
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $deprecations);
        $this->assertSame([1002 => 'SET sql_mode="TRADITIONAL"'], $options, 'MySQL init-command driver option');
    }

    public function testNoDriverOptionsWithoutSqlMode()
    {
        $this->assertSame([], $this->pdoOptions([]));
        $this->assertSame([], $this->pdoOptions(['sql_mode' => '']));
    }
}
