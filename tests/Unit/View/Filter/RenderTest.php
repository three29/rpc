<?php

namespace Tests\Unit\View\Filter;

use RPC\View\Filter\Render;
use RPC\View\Filter;
use Tests\Unit\UnitTestCase;

class RenderTest extends UnitTestCase
{
    private $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new Render();
    }

    public function testClassExists()
    {
        $this->assertTrue(class_exists(Render::class));
    }

    public function testExtendsBaseFilter()
    {
        $this->assertInstanceOf(Filter::class, $this->filter);
    }

    public function testTransformsRenderTag()
    {
        $source = '<render>header.php</render>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('<?php', $result);
        $this->assertStringContainsString('header.php', $result);
        $this->assertStringContainsString('$view->getFilteredFile', $result);
    }

    public function testPreservesTemplateContext()
    {
        $source = '<render>partial.php</render>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('$_rpc_view_old_template', $result);
        $this->assertStringContainsString('$view->getCurrentTemplate()', $result);
        $this->assertStringContainsString('$view->setCurrentTemplate', $result);
    }

    public function testHandlesEmptySource()
    {
        $result = $this->filter->filter('');
        $this->assertEquals('', $result);
    }

    public function testHandlesSourceWithoutRenderTags()
    {
        $source = '<div>No render tags</div>';
        $result = $this->filter->filter($source);

        $this->assertEquals($source, $result);
    }

    public function testMultipleRenderTags()
    {
        $source = '<render>header.php</render><render>footer.php</render>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('header.php', $result);
        $this->assertStringContainsString('footer.php', $result);
    }

    public function testRenderTagWithPathSeparators()
    {
        $source = '<render>partials/menu.php</render>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('partials/menu.php', $result);
    }
}
