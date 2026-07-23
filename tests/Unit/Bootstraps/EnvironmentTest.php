<?php

namespace Tests\Unit\Bootstraps;

use RPC\Bootstraps\Environment;
use RPC\Contracts\Bootstrap;
use RPC\Registry;
use Tests\Unit\UnitTestCase;

class EnvironmentTest extends UnitTestCase
{
    private $tempDir;
    private $originalAppPath;
    private $originalCachePath;

    protected function setUp(): void
    {
        parent::setUp();

        // Save original constants if they exist
        $this->originalAppPath = defined('APP_PATH') ? constant('APP_PATH') : null;
        $this->originalCachePath = defined('CACHE_PATH') ? constant('CACHE_PATH') : null;

        // Create temp directory with config subdirectory
        $this->tempDir = sys_get_temp_dir() . '/rpc_bootstrap_test_' . uniqid();
        mkdir($this->tempDir, 0750, true);
        mkdir($this->tempDir . '/config', 0750, true);

        // Clean registry
        $GLOBALS['_RPC_REGISTRY_'] = [];
    }

    protected function tearDown(): void
    {
        // Clean up temp directory
        if (is_dir($this->tempDir)) {
            $this->recursiveDelete($this->tempDir);
        }

        // Clean registry
        unset($GLOBALS['_RPC_REGISTRY_']);

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

    public function testImplementsBootstrapInterface()
    {
        $this->assertInstanceOf(Bootstrap::class, new Environment());
    }

    public function testHandleThrowsExceptionWhenRootPathNotSet()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Root path not set');

        Environment::handle();
    }

    public function testHandleLoadsEnvironmentFile()
    {
        Registry::set('root_path', $this->tempDir);

        // Create .env file
        file_put_contents($this->tempDir . '/config/.env', "TEST_VAR=test_value\n");

        Environment::handle();

        // Dotenv v5 uses $_ENV instead of getenv()
        $this->assertEquals('test_value', $_ENV['TEST_VAR'] ?? getenv('TEST_VAR'));

        // Cleanup
        unset($_ENV['TEST_VAR']);
        putenv('TEST_VAR');
    }

    public function testHandleDoesNotThrowWhenEnvFileMissing()
    {
        Registry::set('root_path', $this->tempDir);

        // No .env file created - should use safeLoad and not throw
        Environment::handle();

        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function testHandleRespectsExistingConstants()
    {
        Registry::set('root_path', $this->tempDir);

        // Constants are already defined from previous tests or Application setup
        // Just verify handle() doesn't throw when constants exist
        Environment::handle();

        $this->assertTrue(defined('APP_PATH'));
        $this->assertTrue(defined('CACHE_PATH'));
    }

    public function testHandleWithEmptyRootPath()
    {
        Registry::set('root_path', '');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Root path not set');

        Environment::handle();
    }

    public function testHandleWithEnvVariables()
    {
        Registry::set('root_path', $this->tempDir);

        // Create .env file with multiple variables
        $envContent = "APP_NAME=TestApp\nAPP_DEBUG=true\n";
        file_put_contents($this->tempDir . '/config/.env', $envContent);

        Environment::handle();

        // Dotenv v5 uses $_ENV instead of getenv()
        $this->assertEquals('TestApp', $_ENV['APP_NAME'] ?? getenv('APP_NAME'));
        $this->assertEquals('true', $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG'));

        // Cleanup
        unset($_ENV['APP_NAME'], $_ENV['APP_DEBUG']);
        putenv('APP_NAME');
        putenv('APP_DEBUG');
    }
}
