<?php

namespace Tests\Unit\Contracts;

use RPC\Contracts\Bootstrap;
use Tests\Unit\UnitTestCase;

class BootstrapTest extends UnitTestCase
{
    public function testBootstrapInterfaceExists()
    {
        $this->assertTrue(interface_exists(Bootstrap::class));
    }

    public function testBootstrapInterfaceHasHandleMethod()
    {
        $reflection = new \ReflectionClass(Bootstrap::class);

        $this->assertTrue($reflection->hasMethod('handle'));

        $method = $reflection->getMethod('handle');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    public function testBootstrapInterfaceCanBeImplemented()
    {
        $implementation = new class implements Bootstrap {
            public static function handle() {
                return 'bootstrapped';
            }
        };

        $this->assertInstanceOf(Bootstrap::class, $implementation);
        $this->assertEquals('bootstrapped', $implementation::handle());
    }
}
