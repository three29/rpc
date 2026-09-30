<?php

namespace Tests\Unit\Events;

use RPC\Events\ViewRendered;
use RPC\View;
use RPC\View\Cache;
use Tests\Unit\UnitTestCase;

class ViewRenderedTest extends UnitTestCase
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
        $event = new ViewRendered($this->view, 'template.php');

        $this->assertInstanceOf(ViewRendered::class, $event);
    }

    public function testEventContainsView()
    {
        $event = new ViewRendered($this->view, 'template.php');

        $this->assertSame($this->view, $event->view);
    }

    public function testEventContainsTemplate()
    {
        $template = 'templates/user/profile.php';
        $event = new ViewRendered($this->view, $template);

        $this->assertEquals($template, $event->template);
    }

    public function testReadonlyViewProperty()
    {
        $event = new ViewRendered($this->view, 'template.php');

        $this->assertInstanceOf(View::class, $event->view);
    }

    public function testReadonlyTemplateProperty()
    {
        $event = new ViewRendered($this->view, 'template.php');

        $this->assertEquals('template.php', $event->template);
    }
}
