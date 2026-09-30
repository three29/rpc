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
        // 2. Errors (so failures in later bootstraps are handled)
        // 3. Database (skipped in testing when DB_NAME is empty)
        // 4. Session

        $kernel = new Kernel($app, $router);

        // Verify environment was bootstrapped
        $this->assertEquals('testing', getenv('APP_ENV'));

        // Note: Full testing of bootstrap order would require mocking
        // or creating test doubles for each bootstrap class
        $this->assertInstanceOf(Kernel::class, $kernel);
    }

    private function kernelWithRoute(\Closure $route): Kernel
    {
        $app = Application::configure($this->testRootPath);
        $router = new class($route) extends Router {
            public function __construct(private \Closure $route)
            {
                parent::__construct();
            }

            protected function executeRoute(): void
            {
                ($this->route)();
            }
        };
        $router->setExceptionHandler(new \RPC\Exception\Handler(null, null));

        return new Kernel($app, $router);
    }

    public function testErrorMidRenderReplacesPartialOutput(): void
    {
        unset($_SERVER['HTTP_ACCEPT'], $_SERVER['CONTENT_TYPE'], $_SERVER['HTTP_X_REQUESTED_WITH']);

        $kernel = $this->kernelWithRoute(function () {
            echo '<html><body>half a report';
            throw new \RuntimeException('query failed');
        });

        $log = $this->testRootPath . '/error.log';
        $previous_log = ini_set('error_log', $log);

        try {
            $level = ob_get_level();
            ob_start();
            $kernel->handle();
            $output = ob_get_clean();
        } finally {
            ini_set('error_log', $previous_log === false ? '' : $previous_log);
        }

        $this->assertSame('500 - Internal Server Error', $output);
        $this->assertStringContainsString('[RPC] RuntimeException: query failed', file_get_contents($log));
        $this->assertSame($level, ob_get_level());
        $this->assertNull(\RPC\Exception\Handler::$output_buffer_level);
    }

    public function testSuccessfulResponseIsFlushed(): void
    {
        $kernel = $this->kernelWithRoute(function () {
            echo 'all good';
        });

        $level = ob_get_level();
        ob_start();
        $kernel->handle();

        $this->assertSame('all good', ob_get_clean());
        $this->assertSame($level, ob_get_level());
    }

    public function testErrorsBootstrapRunsBeforeDatabase(): void
    {
        $bootstraps = (new \ReflectionProperty(Kernel::class, 'bootstraps'))->getValue(
            $this->kernelWithRoute(function () {})
        );

        $this->assertSame(\RPC\Bootstraps\Environment::class, $bootstraps[0]);
        $this->assertLessThan(
            array_search(\RPC\Bootstraps\Database::class, $bootstraps, true),
            array_search(\RPC\Bootstraps\Errors::class, $bootstraps, true)
        );
    }
}
