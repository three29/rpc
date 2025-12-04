<?php

namespace Tests\Unit\View;

use RPC\View\Filter;
use Tests\Unit\UnitTestCase;

class FilterTest extends UnitTestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists(Filter::class));
    }

    public function testCanInstantiate()
    {
        $filter = new Filter();
        $this->assertInstanceOf(Filter::class, $filter);
    }

    public function testHasFilterMethod()
    {
        $this->assertTrue(method_exists(Filter::class, 'filter'));
    }

    public function testFilterReturnsSourceUnchanged()
    {
        $filter = new Filter();
        $source = '<div>Hello World</div>';

        $result = $filter->filter($source);

        $this->assertEquals($source, $result);
    }

    public function testFilterHandlesEmptyString()
    {
        $filter = new Filter();
        $result = $filter->filter('');

        $this->assertEquals('', $result);
    }

    public function testFilterHandlesMultilineContent()
    {
        $filter = new Filter();
        $source = <<<HTML
<html>
    <body>
        <h1>Test</h1>
    </body>
</html>
HTML;

        $result = $filter->filter($source);

        $this->assertEquals($source, $result);
    }

    public function testFilterMethodSignature()
    {
        $reflection = new \ReflectionMethod(Filter::class, 'filter');

        $this->assertTrue($reflection->isPublic());
        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('source', $params[0]->getName());
    }
}
