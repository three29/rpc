<?php

namespace Tests\Unit\Router;

use RPC\Router\Rewrite;
use Tests\Unit\UnitTestCase;

class RewriteTest extends UnitTestCase
{
    public function testRewriteClassExists()
    {
        $this->assertTrue(class_exists(Rewrite::class));
    }

    public function testRewriteCanBeInstantiated()
    {
        $rewrite = new Rewrite();

        $this->assertInstanceOf(Rewrite::class, $rewrite);
    }

    public function testRewriteIsPlaceholderClass()
    {
        // This class exists as a placeholder for future rewrite functionality
        // It can be instantiated but has no methods or properties defined
        $rewrite = new Rewrite();

        $reflection = new \ReflectionClass($rewrite);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Filter out constructor if it exists
        $customMethods = array_filter($methods, function($method) {
            return !in_array($method->name, ['__construct']);
        });

        $this->assertEmpty($customMethods);
    }
}
