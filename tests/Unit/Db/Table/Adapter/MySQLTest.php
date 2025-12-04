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
        $this->assertEquals('array', $reflection->getMethod('query')->getReturnType()?->getName());
        $this->assertEquals('mixed', $reflection->getMethod('execute')->getReturnType()?->getName());
        $this->assertEquals('mixed', $reflection->getMethod('cacheQuery')->getReturnType()?->getName());
        $this->assertEquals('object', $reflection->getMethod('__callStatic')->getReturnType()?->getName());
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
}
