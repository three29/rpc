<?php

namespace Tests\Unit\View\Filter;

use RPC\View\Filter\Placeholder;
use RPC\View\Filter;
use Tests\Unit\UnitTestCase;

class PlaceholderTest extends UnitTestCase
{
    private $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new Placeholder();
    }

    public function testClassExists()
    {
        $this->assertTrue(class_exists(Placeholder::class));
    }

    public function testExtendsBaseFilter()
    {
        $this->assertInstanceOf(Filter::class, $this->filter);
    }

    public function testTransformsPlaceholderTag()
    {
        $source = '<placeholder id="header"></placeholder>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('<?php if( function_exists', $result);
        $this->assertStringContainsString('_rpc_view_placeholder_header', $result);
    }

    public function testTransformsFillerTag()
    {
        $source = '<filler for="header">Content here</filler>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('<?php function _rpc_view_placeholder_header', $result);
        $this->assertStringContainsString('Content here', $result);
    }

    public function testHandlesEmptySource()
    {
        $result = $this->filter->filter('');
        $this->assertEquals('', $result);
    }

    public function testHandlesSourceWithoutPlaceholders()
    {
        $source = '<div>No placeholders here</div>';
        $result = $this->filter->filter($source);

        $this->assertEquals($source, $result);
    }

    public function testPlaceholderIdWithHyphens()
    {
        $source = '<placeholder id="main-content"></placeholder>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('_rpc_view_placeholder_main-content', $result);
    }

    public function testPlaceholderIdWithUnderscores()
    {
        $source = '<placeholder id="main_content"></placeholder>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('_rpc_view_placeholder_main_content', $result);
    }

    public function testMultiplePlaceholders()
    {
        $source = '<placeholder id="header"></placeholder><placeholder id="footer"></placeholder>';
        $result = $this->filter->filter($source);

        $this->assertStringContainsString('_rpc_view_placeholder_header', $result);
        $this->assertStringContainsString('_rpc_view_placeholder_footer', $result);
    }

    public function testFillerClosingTag()
    {
        $source = '<filler for="test">Content</filler>';
        $result = $this->filter->filter($source);

        // Check closing PHP tag is added
        $this->assertGreaterThan(0, substr_count($result, '$view->setCurrentTemplate'));
    }
}
