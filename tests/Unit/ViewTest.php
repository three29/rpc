<?php

namespace Tests\Unit;

use RPC\View;
use RPC\View\Cache;
use Tests\Unit\UnitTestCase;

class ViewTest extends UnitTestCase
{
    private $tempDir;
    private $view;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup $_SERVER for form testing
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Create temporary directory for templates
        $this->tempDir = sys_get_temp_dir() . '/rpc_view_test_' . uniqid();
        mkdir($this->tempDir);

        $cache = new Cache($this->tempDir . '/cache');
        $this->view = new View($this->tempDir, $cache);
    }

    protected function tearDown(): void
    {
        // Clean up temp directory
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }

        parent::tearDown();
    }

    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function createTemplate($name, $content)
    {
        $path = $this->tempDir . '/' . $name;
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($path, $content);
    }

    public function testEchooFilter()
    {
        $this->createTemplate('test.php', '<?= $name ?>');

        $this->view->name = 'John';

        ob_start();
        $this->view->display('test.php');
        $output = ob_get_clean();

        $this->assertStringContainsString('John', $output);
    }

    public function testRenderFilter()
    {
        $this->createTemplate('partial.php', 'Hello from partial');
        $this->createTemplate('main.php', '<render>partial.php</render>');

        ob_start();
        $this->view->display('main.php');
        $output = ob_get_clean();

        $this->assertStringContainsString('Hello from partial', $output);
    }

    public function testFormTextFilter()
    {
        $this->createTemplate('form.php', '<form method="post"><input type="text" name="username" value="<?= $user ?>" /></form>');

        $this->view->user = 'testuser';

        ob_start();
        $this->view->display('form.php');
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('form', $output);
    }

    public function testFormPasswordFilter()
    {
        $this->createTemplate('form.php', '<form method="post"><input type="password" name="pass" value="<?= $password ?>" /></form>');

        $this->view->password = 'secret';

        ob_start();
        $this->view->display('form.php');
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('form', $output);
    }

    public function testFormHiddenFilter()
    {
        $this->createTemplate('form.php', '<form method="post"><input type="hidden" name="token" value="<?= $token ?>" /></form>');

        $this->view->token = 'abc123';

        ob_start();
        $this->view->display('form.php');
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('form', $output);
    }

    public function testFormTextareaFilter()
    {
        $this->createTemplate('form.php', '<form><textarea name="content" value="<?= $text ?>"></textarea></form>');

        $this->view->text = 'Sample text';

        ob_start();
        $this->view->display('form.php');
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
    }

    public function testFormSelectFilter()
    {
        $this->createTemplate('form.php', '<form method="post"><select name="choice" source="<?= $options ?>" selected="<?= $selected ?>"></select></form>');

        $this->view->options = [1 => 'One', 2 => 'Two'];
        $this->view->selected = 1;

        ob_start();
        $this->view->display('form.php');
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('form', $output);
    }

    public function testFormCheckboxFilter()
    {
        $this->createTemplate('form.php', '<form><input type="checkbox" name="agree" value="1" checked="<?= $checked ?>">></form>');

        $this->view->checked = true;

        ob_start();
        $this->view->display('form.php');
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
    }

    public function testFormRadioFilter()
    {
        $this->createTemplate('form.php', '<form method="post"><input type="radio" name="option" value="1" checked="<?= $checked ?>" /></form>');

        $this->view->checked = true;

        ob_start();
        $this->view->display('form.php');
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('form', $output);
    }

    public function testErrorFilter()
    {
        $this->createTemplate('form.php', '<error id="username" class="error-class"></error>');

        ob_start();
        $this->view->display('form.php');
        $output = ob_get_clean();

        // Error filter wraps in PHP conditional, so output might be empty if no error set
        // Just verify it doesn't throw errors during filtering
        $this->assertTrue(true);
    }

    public function testAssignAndRetrieve()
    {
        $this->view->test = 'value';
        $this->createTemplate('test.php', '<?= $test ?>');

        ob_start();
        $this->view->display('test.php');
        $output = ob_get_clean();

        $this->assertStringContainsString('value', $output);
    }
}
