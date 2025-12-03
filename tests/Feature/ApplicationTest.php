<?php

namespace Tests\Feature;

use RPC\Application;
use RPC\Registry;

class ApplicationTest extends FeatureTestCase
{
    private string $testRootPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testRootPath = sys_get_temp_dir() . '/rpc_test_' . uniqid();
        mkdir($this->testRootPath);
        mkdir($this->testRootPath . '/config');
        mkdir($this->testRootPath . '/APP');
        mkdir($this->testRootPath . '/tmp');
        mkdir($this->testRootPath . '/tmp/cache');

        // Create a minimal .env file for testing
        file_put_contents($this->testRootPath . '/config/.env', 'TEST_VAR=test_value');
    }

    protected function tearDown(): void
    {
        // Clean up test directory
        if (is_dir($this->testRootPath)) {
            $this->removeDirectory($this->testRootPath);
        }

        parent::tearDown();
    }

    private function removeDirectory(string $dir): void
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

    public function testConfigureRequiresRootPath(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Root path not set');

        Application::configure('');
    }

    public function testConfigureReturnsApplicationInstance(): void
    {
        $app = Application::configure($this->testRootPath);

        $this->assertInstanceOf(Application::class, $app);
    }

    public function testConfigureSetsRootPathInRegistry(): void
    {
        Application::configure($this->testRootPath);

        $this->assertEquals($this->testRootPath, Registry::get('root_path'));
    }

    public function testConfigureReturnsSameInstanceOnSubsequentCalls(): void
    {
        $app1 = Application::configure($this->testRootPath);
        $app2 = Application::configure($this->testRootPath);

        $this->assertSame($app1, $app2, 'Application should return the same instance');
    }

    public function testConfigureSetsAppPathConstant(): void
    {
        // Note: Constants can only be defined once per process
        // This test verifies the constant is set, but path may vary if already set
        Application::configure($this->testRootPath);

        $this->assertTrue(defined('APP_PATH'));
        $this->assertStringContainsString('/APP', APP_PATH);
    }

    public function testConfigureSetsCachePathConstant(): void
    {
        Application::configure($this->testRootPath);

        $this->assertTrue(defined('CACHE_PATH'));
        $this->assertStringContainsString('/tmp/cache', CACHE_PATH);
    }

    public function testCreateMethodReturnsApplication(): void
    {
        $app = Application::configure($this->testRootPath);
        $created = $app->create();

        $this->assertInstanceOf(Application::class, $created);
        $this->assertSame($app, $created);
    }

    public function testEnvironmentVariablesAreLoaded(): void
    {
        Application::configure($this->testRootPath);

        // Check if environment variable was loaded from .env file
        // getenv() may not work with dotenv v5, use $_ENV instead
        $this->assertTrue(
            getenv('TEST_VAR') === 'test_value' ||
            (isset($_ENV['TEST_VAR']) && $_ENV['TEST_VAR'] === 'test_value')
        );
    }
}
