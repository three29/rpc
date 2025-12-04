<?php

namespace Tests\Unit\Contracts;

use RPC\Contracts\Container;
use Psr\Container\ContainerInterface;
use Tests\Unit\UnitTestCase;

class ContainerTest extends UnitTestCase
{
    public function testContainerInterfaceExists()
    {
        $this->assertTrue(interface_exists(Container::class));
    }

    public function testContainerExtendsPsr11()
    {
        $reflection = new \ReflectionClass(Container::class);

        $this->assertTrue($reflection->implementsInterface(ContainerInterface::class));
    }

    public function testContainerHasPsr11Methods()
    {
        $reflection = new \ReflectionClass(Container::class);

        // PSR-11 required methods
        $this->assertTrue($reflection->hasMethod('get'));
        $this->assertTrue($reflection->hasMethod('has'));

        $getMethod = $reflection->getMethod('get');
        $this->assertTrue($getMethod->isPublic());
        $this->assertEquals(1, $getMethod->getNumberOfRequiredParameters());

        $hasMethod = $reflection->getMethod('has');
        $this->assertTrue($hasMethod->isPublic());
        $this->assertEquals(1, $hasMethod->getNumberOfRequiredParameters());
    }

    public function testContainerHasBindMethod()
    {
        $reflection = new \ReflectionClass(Container::class);

        $this->assertTrue($reflection->hasMethod('bind'));

        $method = $reflection->getMethod('bind');
        $this->assertTrue($method->isPublic());
        $this->assertEquals(1, $method->getNumberOfRequiredParameters());
    }

    public function testContainerHasSingletonMethod()
    {
        $reflection = new \ReflectionClass(Container::class);

        $this->assertTrue($reflection->hasMethod('singleton'));

        $method = $reflection->getMethod('singleton');
        $this->assertTrue($method->isPublic());
        $this->assertEquals(1, $method->getNumberOfRequiredParameters());
    }

    public function testContainerHasInstanceMethod()
    {
        $reflection = new \ReflectionClass(Container::class);

        $this->assertTrue($reflection->hasMethod('instance'));

        $method = $reflection->getMethod('instance');
        $this->assertTrue($method->isPublic());
        $this->assertEquals(2, $method->getNumberOfRequiredParameters());
    }

    public function testContainerHasMakeMethod()
    {
        $reflection = new \ReflectionClass(Container::class);

        $this->assertTrue($reflection->hasMethod('make'));

        $method = $reflection->getMethod('make');
        $this->assertTrue($method->isPublic());
        $this->assertEquals(1, $method->getNumberOfRequiredParameters());
    }

    public function testContainerHasBoundMethod()
    {
        $reflection = new \ReflectionClass(Container::class);

        $this->assertTrue($reflection->hasMethod('bound'));

        $method = $reflection->getMethod('bound');
        $this->assertTrue($method->isPublic());
        $this->assertEquals(1, $method->getNumberOfRequiredParameters());
    }

    public function testContainerHasForgetMethod()
    {
        $reflection = new \ReflectionClass(Container::class);

        $this->assertTrue($reflection->hasMethod('forget'));

        $method = $reflection->getMethod('forget');
        $this->assertTrue($method->isPublic());
        $this->assertEquals(1, $method->getNumberOfRequiredParameters());
    }

    public function testContainerHasFlushMethod()
    {
        $reflection = new \ReflectionClass(Container::class);

        $this->assertTrue($reflection->hasMethod('flush'));

        $method = $reflection->getMethod('flush');
        $this->assertTrue($method->isPublic());
        $this->assertEquals(0, $method->getNumberOfRequiredParameters());
    }

    public function testContainerCanBeImplemented()
    {
        $implementation = new class implements Container {
            private array $bindings = [];
            private array $instances = [];

            public function get(string $id)
            {
                return $this->instances[$id] ?? null;
            }

            public function has(string $id): bool
            {
                return isset($this->bindings[$id]) || isset($this->instances[$id]);
            }

            public function bind(string $abstract, mixed $concrete = null, bool $shared = false): void
            {
                $this->bindings[$abstract] = ['concrete' => $concrete, 'shared' => $shared];
            }

            public function singleton(string $abstract, mixed $concrete = null): void
            {
                $this->bind($abstract, $concrete, true);
            }

            public function instance(string $abstract, mixed $instance): mixed
            {
                $this->instances[$abstract] = $instance;
                return $instance;
            }

            public function make(string $abstract, mixed $default = null): mixed
            {
                return $this->get($abstract) ?? $default;
            }

            public function bound(string $abstract): bool
            {
                return $this->has($abstract);
            }

            public function forget(string $abstract): void
            {
                unset($this->instances[$abstract], $this->bindings[$abstract]);
            }

            public function flush(): void
            {
                $this->bindings = [];
                $this->instances = [];
            }
        };

        $this->assertInstanceOf(Container::class, $implementation);
        $this->assertInstanceOf(ContainerInterface::class, $implementation);

        // Test basic functionality
        $implementation->instance('test', 'value');
        $this->assertTrue($implementation->has('test'));
        $this->assertEquals('value', $implementation->get('test'));

        $implementation->forget('test');
        $this->assertFalse($implementation->has('test'));
    }
}
