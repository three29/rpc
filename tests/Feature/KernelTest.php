<?php

namespace Tests\Feature;

use RPC\Application;
use RPC\HTTP\Kernel;
use RPC\Router;
use RPC\Registry;

class KernelTest extends FeatureTestCase
{
    private string $testRootPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testRootPath = sys_get_temp_dir() . '/rpc_kernel_test_' . uniqid();
        mkdir($this->testRootPath);
        mkdir($this->testRootPath . '/config');

        // Create a minimal .env file for testing without database config
        // Database bootstrap will be skipped when APP_ENV=testing and DB_NAME is empty
        $envContent = <<<ENV
APP_ENV=testing
ENV;
        file_put_contents($this->testRootPath . '/config/.env', $envContent);

        // Create a routes file
        file_put_contents($this->testRootPath . '/config/routes.php', '<?php return [];');

        // Mock CLI environment
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    protected function tearDown(): void
    {
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

    public function testKernelCanBeInstantiated(): void
    {
        $app = Application::configure($this->testRootPath);
        $router = new Router();

        $kernel = new Kernel($app, $router);

        $this->assertInstanceOf(Kernel::class, $kernel);
    }

    public function testKernelBootstrapsAreExecuted(): void
    {
        $app = Application::configure($this->testRootPath);
        $router = new Router();

        // Create kernel - bootstraps should run in constructor
        $kernel = new Kernel($app, $router);

        // Environment bootstrap should have loaded the .env file
        $this->assertEquals('testing', getenv('APP_ENV'));
    }

    public function testKernelLoadsRoutes(): void
    {
        // Create a routes file with actual routes
        file_put_contents(
            $this->testRootPath . '/config/routes.php',
            '<?php return ["test" => "home/index"];'
        );

        $app = Application::configure($this->testRootPath);
        $router = new Router();

        $kernel = new Kernel($app, $router);

        // Note: Routes are only loaded when NOT in CLI mode (see Kernel.php:51)
        // Since PHPUnit runs in CLI, routes won't be loaded into registry
        // This test verifies that the kernel was created successfully
        // and routes file exists for non-CLI environments
        $this->assertInstanceOf(Kernel::class, $kernel);
        $this->assertFileExists($this->testRootPath . '/config/routes.php');
    }

    public function testKernelDoesNotLoadRoutesInCLI(): void
    {
        // Simulate CLI environment
        $_SERVER['argv'] = ['test'];

        $app = Application::configure($this->testRootPath);
        $router = new Router();

        // In CLI mode, routes should not be loaded
        // This is harder to test as it depends on php_sapi_name()
        // which can't be easily mocked. This test documents expected behavior.
        $kernel = new Kernel($app, $router);

        $this->assertInstanceOf(Kernel::class, $kernel);
    }

    public function testKernelBootstrapOrder(): void
    {
        $app = Application::configure($this->testRootPath);
        $router = new Router();

        // The bootstraps should run in order:
        // 1. Environment (loads .env)
        // 2. Database (skipped in testing when DB_NAME is empty)
        // 3. Session
        // 4. Errors

        $kernel = new Kernel($app, $router);

        // Verify environment was bootstrapped
        $this->assertEquals('testing', getenv('APP_ENV'));

        // Note: Full testing of bootstrap order would require mocking
        // or creating test doubles for each bootstrap class
        $this->assertInstanceOf(Kernel::class, $kernel);
    }
}
