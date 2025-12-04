<?php

namespace Tests\Unit\Registry;

use RPC\Registry\ContainerAdapter;
use RPC\Registry\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Tests\Unit\UnitTestCase;

class ContainerAdapterTest extends UnitTestCase
{
    private ContainerAdapter $adapter;

    protected function setUp(): void
    {
        parent::setUp();

        // Clean registry
        $GLOBALS['_RPC_REGISTRY_'] = [];

        $this->adapter = new ContainerAdapter();
    }

    protected function tearDown(): void
    {
        // Clean up registry
        unset($GLOBALS['_RPC_REGISTRY_']);

        parent::tearDown();
    }

    public function testAdapterImplementsPsr11()
    {
        $this->assertInstanceOf(ContainerInterface::class, $this->adapter);
    }

    public function testHasReturnsFalseForNonexistentEntry()
    {
        $this->assertFalse($this->adapter->has('nonexistent'));
    }

    public function testHasReturnsTrueForExistingEntry()
    {
        $GLOBALS['_RPC_REGISTRY_']['test'] = 'value';

        $this->assertTrue($this->adapter->has('test'));
    }

    public function testGetReturnsValueFromRegistry()
    {
        $GLOBALS['_RPC_REGISTRY_']['test'] = 'expected value';

        $this->assertEquals('expected value', $this->adapter->get('test'));
    }

    public function testGetThrowsNotFoundExceptionForNonexistentEntry()
    {
        $this->expectException(NotFoundException::class);
        $this->expectException(NotFoundExceptionInterface::class);
        $this->expectExceptionMessage("Entry 'missing' not found in container");

        $this->adapter->get('missing');
    }

    public function testGetThrowsExceptionForNullValue()
    {
        // Note: isset() returns false for null values, so the adapter cannot
        // distinguish between missing entries and null values
        $GLOBALS['_RPC_REGISTRY_']['null_value'] = null;

        $this->expectException(NotFoundException::class);
        $this->adapter->get('null_value');
    }

    public function testGetReturnsFalseValue()
    {
        $GLOBALS['_RPC_REGISTRY_']['false_value'] = false;

        $this->assertFalse($this->adapter->get('false_value'));
    }

    public function testGetReturnsArray()
    {
        $expected = ['key' => 'value', 'nested' => ['data' => 123]];
        $GLOBALS['_RPC_REGISTRY_']['array'] = $expected;

        $this->assertEquals($expected, $this->adapter->get('array'));
    }

    public function testGetReturnsObject()
    {
        $object = new \stdClass();
        $object->property = 'value';
        $GLOBALS['_RPC_REGISTRY_']['object'] = $object;

        $this->assertSame($object, $this->adapter->get('object'));
    }

    public function testHasWithNullValue()
    {
        $GLOBALS['_RPC_REGISTRY_']['null_key'] = null;

        // isset() returns false for null values
        $this->assertFalse($this->adapter->has('null_key'));
    }

    public function testMultipleEntries()
    {
        $GLOBALS['_RPC_REGISTRY_']['first'] = 'value1';
        $GLOBALS['_RPC_REGISTRY_']['second'] = 'value2';
        $GLOBALS['_RPC_REGISTRY_']['third'] = 'value3';

        $this->assertTrue($this->adapter->has('first'));
        $this->assertTrue($this->adapter->has('second'));
        $this->assertTrue($this->adapter->has('third'));

        $this->assertEquals('value1', $this->adapter->get('first'));
        $this->assertEquals('value2', $this->adapter->get('second'));
        $this->assertEquals('value3', $this->adapter->get('third'));
    }

    public function testRegistryModificationReflectsInAdapter()
    {
        $this->assertFalse($this->adapter->has('dynamic'));

        $GLOBALS['_RPC_REGISTRY_']['dynamic'] = 'added';

        $this->assertTrue($this->adapter->has('dynamic'));
        $this->assertEquals('added', $this->adapter->get('dynamic'));

        unset($GLOBALS['_RPC_REGISTRY_']['dynamic']);

        $this->assertFalse($this->adapter->has('dynamic'));
    }
}
