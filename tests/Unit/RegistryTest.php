<?php

namespace Tests\Unit;

use RPC\Registry;

class RegistryTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear global registry
        $GLOBALS['_RPC_REGISTRY_'] = [];

        // Clear Application container if it exists
        if (\RPC\Application::$app !== null) {
            \RPC\Application::$app->flush();
        }
    }

    public function testSetAndGet(): void
    {
        $testObject = new \stdClass();
        $testObject->value = 'test';

        Registry::set('test_key', $testObject);
        $retrieved = Registry::get('test_key');

        $this->assertSame($testObject, $retrieved);
        $this->assertEquals('test', $retrieved->value);
    }

    public function testGetNonExistentKey(): void
    {
        $result = Registry::get('non_existent');
        $this->assertNull($result);
    }

    public function testRegistered(): void
    {
        $this->assertFalse(Registry::registered('test_key'));

        Registry::set('test_key', 'value');

        $this->assertTrue(Registry::registered('test_key'));
    }

    public function testSetReturnsObject(): void
    {
        $testObject = new \stdClass();
        $returned = Registry::set('key', $testObject);

        $this->assertSame($testObject, $returned);
    }

    public function testSetOverwritesExistingValue(): void
    {
        Registry::set('key', 'first');
        Registry::set('key', 'second');

        $this->assertEquals('second', Registry::get('key'));
    }

    public function testMultipleKeys(): void
    {
        Registry::set('key1', 'value1');
        Registry::set('key2', 'value2');
        Registry::set('key3', 'value3');

        $this->assertEquals('value1', Registry::get('key1'));
        $this->assertEquals('value2', Registry::get('key2'));
        $this->assertEquals('value3', Registry::get('key3'));
    }

    public function testDifferentValueTypes(): void
    {
        Registry::set('string', 'test');
        Registry::set('int', 123);
        Registry::set('array', [1, 2, 3]);
        Registry::set('object', new \stdClass());
        Registry::set('bool', true);

        $this->assertEquals('test', Registry::get('string'));
        $this->assertEquals(123, Registry::get('int'));
        $this->assertEquals([1, 2, 3], Registry::get('array'));
        $this->assertInstanceOf(\stdClass::class, Registry::get('object'));
        $this->assertTrue(Registry::get('bool'));
    }
}
