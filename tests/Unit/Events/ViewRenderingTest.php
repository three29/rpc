<?php

namespace Tests\Unit\Events;

use RPC\Events\ViewRendering;
use RPC\View;
use RPC\View\Cache;
use Psr\EventDispatcher\StoppableEventInterface;
use Tests\Unit\UnitTestCase;

class ViewRenderingTest extends UnitTestCase
{
    private $tempDir;
    private $view;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/rpc_event_test_' . uniqid();
        mkdir($this->tempDir, 0750, true);
        mkdir($this->tempDir . '/cache', 0750, true);

        $cache = new Cache($this->tempDir . '/cache');
        $this->view = new View($this->tempDir, $cache);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->recursiveDelete($this->tempDir);
        }

        parent::tearDown();
    }

    private function recursiveDelete($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->recursiveDelete($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testEventConstruction()
    {
        $event = new ViewRendering($this->view, 'template.php');

        $this->assertInstanceOf(ViewRendering::class, $event);
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
    }

    public function testEventContainsView()
    {
        $event = new ViewRendering($this->view, 'template.php');

        $this->assertSame($this->view, $event->view);
    }

    public function testEventContainsTemplate()
    {
        $template = 'templates/user/profile.php';
        $event = new ViewRendering($this->view, $template);

        $this->assertEquals($template, $event->template);
    }

    public function testPropagationNotStoppedByDefault()
    {
        $event = new ViewRendering($this->view, 'template.php');

        $this->assertFalse($event->isPropagationStopped());
    }

    public function testCancelRendering()
    {
        $event = new ViewRendering($this->view, 'template.php');

        $event->cancelRendering();

        $this->assertTrue($event->isPropagationStopped());
    }

    public function testReadonlyViewProperty()
    {
        $event = new ViewRendering($this->view, 'template.php');

        $this->assertInstanceOf(View::class, $event->view);
    }

    public function testReadonlyTemplateProperty()
    {
        $event = new ViewRendering($this->view, 'template.php');

        $this->assertEquals('template.php', $event->template);
    }
}
