<?php

namespace Tests\Unit;

use RPC\Db;
use RPC\Exception\DatabaseException;
use RPC\Exception\InvalidArgumentException;
use Tests\Unit\UnitTestCase;

class DbTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear any existing connections before each test
        $reflection = new \ReflectionClass(Db::class);

        $connections = $reflection->getProperty('connections');
        $connections->setValue(null, []);

        $instances = $reflection->getProperty('instances');
        $instances->setValue(null, []);
    }

    public function testAddConnection()
    {
        Db::addConnection('test', [
            'adapter' => 'MySQL',
            'hostname' => 'localhost',
            'database' => 'test_db',
            'socket' => null,
            'port' => 3306,
            'username' => 'root',
            'password' => '',
            'prefix' => ''
        ]);

        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function testAddConnectionRequiresName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configuration array should have a database name set');

        Db::addConnection('', [
            'adapter' => 'MySQL',
            'database' => 'test_db'
        ]);
    }

    public function testAddConnectionRequiresDatabase()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configuration array should have a database adapter set');

        Db::addConnection('test', [
            'adapter' => 'MySQL'
        ]);
    }

    public function testAddConnectionWithEmptyArray()
    {
        // Type hints prevent passing non-array, so test empty array behavior
        $this->expectException(InvalidArgumentException::class);

        Db::addConnection('test', []);
    }

    public function testAddMultipleConnections()
    {
        Db::addConnections([
            'db1' => [
                'adapter' => 'MySQL',
                'hostname' => 'localhost',
                'database' => 'test_db1',
                'socket' => null,
                'port' => 3306,
                'username' => 'root',
                'password' => '',
                'prefix' => ''
            ],
            'db2' => [
                'adapter' => 'MySQL',
                'hostname' => 'localhost',
                'database' => 'test_db2',
                'socket' => null,
                'port' => 3306,
                'username' => 'root',
                'password' => '',
                'prefix' => ''
            ]
        ]);

        $this->assertTrue(true);
    }

    public function testFactoryThrowsExceptionWhenNoConnections()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('No connections loaded');

        Db::factory();
    }

    public function testFactoryThrowsExceptionForUnknownConnection()
    {
        Db::addConnection('test', [
            'adapter' => 'MySQL',
            'hostname' => 'localhost',
            'database' => 'test_db',
            'socket' => null,
            'port' => 3306,
            'username' => 'root',
            'password' => '',
            'prefix' => ''
        ]);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Connection unknown is not loaded');

        Db::factory('unknown');
    }

    public function testSetDefaultConnectionRequiresExistingConnection()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Connection not loaded');

        Db::setDefaultConnection('nonexistent');
    }

    public function testSetDefaultConnection()
    {
        Db::addConnection('test', [
            'adapter' => 'MySQL',
            'hostname' => 'localhost',
            'database' => 'test_db',
            'socket' => null,
            'port' => 3306,
            'username' => 'root',
            'password' => '',
            'prefix' => ''
        ]);

        Db::setDefaultConnection('test');

        $this->assertTrue(true);
    }

    public function testConstants()
    {
        $this->assertEquals(\PDO::FETCH_NUM, Db::FETCH_NUM);
        $this->assertEquals(\PDO::FETCH_ASSOC, Db::FETCH_ASSOC);
        $this->assertEquals(\PDO::FETCH_OBJ, Db::FETCH_OBJ);
        $this->assertEquals('insert', Db::QUERY_INSERT);
        $this->assertEquals('update', Db::QUERY_UPDATE);
    }
}
