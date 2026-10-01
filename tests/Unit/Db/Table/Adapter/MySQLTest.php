<?php

namespace Tests\Unit\Db\Table\Adapter;

use RPC\Db\Table\Adapter\MySQL;
use RPC\Db\Table\Adapter;
use Tests\Unit\UnitTestCase;

class MySQLTest extends UnitTestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists(MySQL::class));
    }

    public function testExtendsBaseAdapter()
    {
        $reflection = new \ReflectionClass(MySQL::class);
        $this->assertTrue($reflection->isSubclassOf(Adapter::class));
    }

    public function testHasLoadFieldsMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, 'loadFields'));
    }

    public function testHasStaticQueryMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, 'query'));

        $reflection = new \ReflectionMethod(MySQL::class, 'query');
        $this->assertTrue($reflection->isStatic());
    }

    public function testHasStaticExecuteMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, 'execute'));

        $reflection = new \ReflectionMethod(MySQL::class, 'execute');
        $this->assertTrue($reflection->isStatic());
    }

    public function testHasGetMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, 'get'));

        $reflection = new \ReflectionMethod(MySQL::class, 'get');
        $this->assertEquals('array', $reflection->getReturnType()?->getName());
    }

    public function testHasGetAllMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, 'getAll'));

        $reflection = new \ReflectionMethod(MySQL::class, 'getAll');
        $this->assertEquals('array', $reflection->getReturnType()?->getName());
    }

    public function testHasGetBySqlMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, 'getBySql'));

        $reflection = new \ReflectionMethod(MySQL::class, 'getBySql');
        $returnType = $reflection->getReturnType();
        $this->assertInstanceOf(\ReflectionUnionType::class, $returnType);
    }

    public function testHasFindMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, 'find'));

        $reflection = new \ReflectionMethod(MySQL::class, 'find');
        $returnType = $reflection->getReturnType();
        $this->assertInstanceOf(\ReflectionUnionType::class, $returnType);
    }

    public function testHasFindAllMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, 'findAll'));

        $reflection = new \ReflectionMethod(MySQL::class, 'findAll');
        $this->assertEquals('array', $reflection->getReturnType()?->getName());
    }

    public function testHasFindBySqlMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, 'findBySql'));

        $reflection = new \ReflectionMethod(MySQL::class, 'findBySql');
        $returnType = $reflection->getReturnType();
        $this->assertInstanceOf(\ReflectionUnionType::class, $returnType);
    }

    public function testHasInsertRowMethod()
    {
        $reflection = new \ReflectionClass(MySQL::class);
        $this->assertTrue($reflection->hasMethod('insertRow'));

        $method = $reflection->getMethod('insertRow');
        $this->assertTrue($method->isProtected());
    }

    public function testHasUpdateRowMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, 'updateRow'));

        $reflection = new \ReflectionMethod(MySQL::class, 'updateRow');
        $this->assertTrue($reflection->isPublic());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('row', $params[0]->getName());
    }

    public function testHasDeleteByMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, 'deleteBy'));

        $reflection = new \ReflectionMethod(MySQL::class, 'deleteBy');
        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('field', $params[0]->getName());
        $this->assertEquals('value', $params[1]->getName());
    }

    public function testHasLockMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, 'lock'));

        $reflection = new \ReflectionMethod(MySQL::class, 'lock');
        $this->assertEquals('void', $reflection->getReturnType()?->getName());
    }

    public function testHasUnlockMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, 'unlock'));

        $reflection = new \ReflectionMethod(MySQL::class, 'unlock');
        $this->assertEquals('void', $reflection->getReturnType()?->getName());
    }

    public function testHasCacheQueryMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, 'cacheQuery'));

        $reflection = new \ReflectionMethod(MySQL::class, 'cacheQuery');
        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('sql', $params[0]->getName());
        $this->assertEquals('seconds', $params[1]->getName());
    }

    public function testHasNewObjectMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, 'newObject'));

        $reflection = new \ReflectionMethod(MySQL::class, 'newObject');
        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('row', $params[0]->getName());

        $returnType = $reflection->getReturnType();
        $this->assertEquals('RPC\Db\Table\Row', $returnType?->getName());
    }

    public function testHasMagicCallStaticMethod()
    {
        $this->assertTrue(method_exists(MySQL::class, '__callStatic'));

        $reflection = new \ReflectionMethod(MySQL::class, '__callStatic');
        $this->assertTrue($reflection->isStatic());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('name', $params[0]->getName());
        $this->assertEquals('arguments', $params[1]->getName());
    }

    public function testMethodReturnTypes()
    {
        $reflection = new \ReflectionClass(MySQL::class);

        // Verify critical method return types
        $this->assertEquals('array|bool|null', (string) $reflection->getMethod('query')->getReturnType());
        $this->assertEquals('array|int|bool|null', (string) $reflection->getMethod('execute')->getReturnType());
        $this->assertEquals('mixed', (string) $reflection->getMethod('cacheQuery')->getReturnType());
        $this->assertEquals('object', (string) $reflection->getMethod('__callStatic')->getReturnType());
    }

    public function testImplementsAllAbstractMethods()
    {
        $reflection = new \ReflectionClass(MySQL::class);

        // Verify all abstract methods from parent are implemented
        $abstractMethods = [
            'loadFields',
            'get',
            'getAll',
            'getBySql',
            'find',
            'findAll',
            'findBySql',
            'deleteBy',
            'insertRow',
            'updateRow',
            'lock',
            'unlock'
        ];

        foreach ($abstractMethods as $method) {
            $this->assertTrue($reflection->hasMethod($method), "Missing method: $method");
        }
    }

    public function testAdditionalConvenienceMethods()
    {
        // Test that MySQL adapter has additional static convenience methods
        $this->assertTrue(method_exists(MySQL::class, 'query'));
        $this->assertTrue(method_exists(MySQL::class, 'execute'));
        $this->assertTrue(method_exists(MySQL::class, 'cacheQuery'));
        $this->assertTrue(method_exists(MySQL::class, 'newObject'));
    }

    private function quoteColumn(string|int $column): string
    {
        $reflection = new \ReflectionClass(MySQL::class);
        $adapter = $reflection->newInstanceWithoutConstructor();

        return $reflection->getMethod('quoteColumn')->invoke($adapter, $column);
    }

    public function testQuoteColumnQuotesPlainAndQualifiedNames()
    {
        $this->assertSame('`email`', $this->quoteColumn('email'));
        $this->assertSame('`u`.`email`', $this->quoteColumn('u.email'));
    }

    public function testQuoteColumnRejectsInjectionInConditionKeys()
    {
        foreach (['1=1 or id', 'id`', 'id"', 'email = ? or 1=1 --', 'a.b.c', '', 0] as $key) {
            try {
                $this->quoteColumn($key);
                $this->fail('Accepted column ' . var_export($key, true));
            } catch (\RPC\Exception\InvalidArgumentException $e) {
                $this->assertStringContainsString('Invalid column name', $e->getMessage());
            }
        }
    }

    /**
     * Adapter wired to a mock connection that records every query
     */
    private function adapterWithDb(\RPC\Db\Adapter $db): MySQL
    {
        $reflection = new \ReflectionClass(MySQL::class);
        $adapter = $reflection->newInstanceWithoutConstructor();

        foreach (['db' => $db, 'name' => 'users', 'pk' => 'id', 'fields' => ['id', 'email']] as $property => $value) {
            $prop = $reflection->getProperty($property);
            $prop->setValue($adapter, $value);
        }

        return $adapter;
    }

    public function testFindersRejectLoneStringConditions()
    {
        $db = $this->createMock(\RPC\Db\Adapter::class);
        $db->expects($this->never())->method('query');
        $db->expects($this->never())->method('prepare');
        $adapter = $this->adapterWithDb($db);

        $payloads = ['0 OR SLEEP(5) -- ?', "1 UNION SELECT password FROM users -- ?", 'email', '1e3', ' 1', '0x1A', '1.5', 1.5, true];

        foreach (['find', 'findAll', 'get', 'getAll', 'getBySql', 'findBySql'] as $method) {
            foreach ($payloads as $payload) {
                try {
                    $adapter->$method($payload);
                    $this->fail($method . '() accepted ' . var_export($payload, true));
                } catch (\RPC\Exception\InvalidArgumentException $e) {
                    $this->addToAssertionCount(1);
                }
            }
        }
    }

    public function testFindByNumericIdBindsThePrimaryKey()
    {
        $statement = $this->createMock(\RPC\Db\Statement::class);
        $statement->expects($this->exactly(2))->method('execute')
            ->with($this->logicalOr($this->identicalTo(['5']), $this->identicalTo([5])))
            ->willReturn([]);

        $db = $this->createMock(\RPC\Db\Adapter::class);
        $db->expects($this->never())->method('query');
        $db->expects($this->exactly(2))->method('prepare')
            ->with($this->stringContains(' id = ? '))
            ->willReturn($statement);

        $adapter = $this->adapterWithDb($db);

        $this->assertNull($adapter->find('5'));
        $this->assertNull($adapter->find(5));
    }

    public function testBoundConditionsStillWork()
    {
        $statement = $this->createMock(\RPC\Db\Statement::class);
        $statement->expects($this->exactly(3))->method('execute')
            ->with($this->logicalOr($this->identicalTo(['a@example.com']), $this->identicalTo(['a@example.com', 'active'])))
            ->willReturn([]);

        $db = $this->createMock(\RPC\Db\Adapter::class);
        $db->expects($this->never())->method('query');
        $db->expects($this->exactly(3))->method('prepare')->willReturn($statement);

        $adapter = $this->adapterWithDb($db);

        // column name with a scalar value gets " = ?" appended
        $this->assertNull($adapter->find('email', 'a@example.com'));
        $this->assertSame([], $adapter->findAll('email = ? AND status = ?', ['a@example.com', 'active']));
        $this->assertSame([], $adapter->findBySql('select * from users where email = ?', ['a@example.com']));
    }
}
