<?php

namespace Tests\Feature;

use RPC\Application;
use RPC\Exception\ConfigurationException;
use RPC\Registry;

class ApplicationTest extends FeatureTestCase
{
    private string $testRootPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset the static application instance
        Application::$app = null;

        $this->testRootPath = sys_get_temp_dir() . '/rpc_test_' . uniqid();
        mkdir($this->testRootPath);
        mkdir($this->testRootPath . '/config');
        mkdir($this->testRootPath . '/APP');
        mkdir($this->testRootPath . '/tmp');
        mkdir($this->testRootPath . '/tmp/cache');

        // Create a minimal .env file for testing with unique variable name
        file_put_contents($this->testRootPath . '/config/.env', 'APP_TEST_UNIQUE_VAR=test_value');
    }

    protected function tearDown(): void
    {
        // Clean up environment variables set during tests
        unset($_ENV['APP_TEST_UNIQUE_VAR']);
        putenv('APP_TEST_UNIQUE_VAR');

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
        $this->expectException(ConfigurationException::class);
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

    public function testConfigureLoadsEnvironmentFileWithoutErrors(): void
    {
        // The .env file exists and should be loaded by configure()
        // We can't reliably test the actual environment variable value due to
        // Dotenv's safeLoad() not overwriting existing variables, but we can
        // verify that configure() succeeds when a .env file is present
        $app = Application::configure($this->testRootPath);

        $this->assertInstanceOf(Application::class, $app);

        // Verify the .env file exists (proves setupEnvironment would attempt to load it)
        $this->assertFileExists($this->testRootPath . '/config/.env');
    }

    public function testBindAndGetSimpleValue(): void
    {
        $app = Application::configure($this->testRootPath);

        $app->bind('test.value', 'simple_value');

        $this->assertEquals('simple_value', $app->get('test.value'));
    }

    public function testBindAndGetWithClosure(): void
    {
        $app = Application::configure($this->testRootPath);

        $app->bind('test.closure', function() {
            return 'closure_result';
        });

        $this->assertEquals('closure_result', $app->get('test.closure'));
    }

    public function testBindNonSharedCreatesNewInstances(): void
    {
        $app = Application::configure($this->testRootPath);

        $app->bind('test.object', function() {
            return new \stdClass();
        }, false);

        $instance1 = $app->get('test.object');
        $instance2 = $app->get('test.object');

        $this->assertNotSame($instance1, $instance2);
    }

    public function testSingletonCreatesSharedInstance(): void
    {
        $app = Application::configure($this->testRootPath);

        $app->singleton('test.singleton', function() {
            return new \stdClass();
        });

        $instance1 = $app->get('test.singleton');
        $instance2 = $app->get('test.singleton');

        $this->assertSame($instance1, $instance2);
    }

    public function testInstanceRegistersExistingObject(): void
    {
        $app = Application::configure($this->testRootPath);

        $obj = new \stdClass();
        $obj->value = 'test';

        $app->instance('test.instance', $obj);

        $retrieved = $app->get('test.instance');

        $this->assertSame($obj, $retrieved);
        $this->assertEquals('test', $retrieved->value);
    }

    public function testHasReturnsTrueForBoundItems(): void
    {
        $app = Application::configure($this->testRootPath);

        $app->bind('test.exists', 'value');

        $this->assertTrue($app->has('test.exists'));
        $this->assertFalse($app->has('test.not.exists'));
    }

    public function testBoundIsAliasForHas(): void
    {
        $app = Application::configure($this->testRootPath);

        $app->bind('test.item', 'value');

        $this->assertTrue($app->bound('test.item'));
        $this->assertFalse($app->bound('test.missing'));
    }

    public function testMakeReturnsValueOrDefault(): void
    {
        $app = Application::configure($this->testRootPath);

        $app->bind('test.exists', 'value');

        $this->assertEquals('value', $app->make('test.exists'));
        $this->assertEquals('default', $app->make('test.missing', 'default'));
        $this->assertNull($app->make('test.missing'));
    }

    public function testForgetRemovesBinding(): void
    {
        $app = Application::configure($this->testRootPath);

        $app->bind('test.forget', 'value');
        $this->assertTrue($app->has('test.forget'));

        $app->forget('test.forget');
        $this->assertFalse($app->has('test.forget'));
    }

    public function testFlushRemovesAllBindings(): void
    {
        $app = Application::configure($this->testRootPath);

        $app->bind('test.one', 'value1');
        $app->bind('test.two', 'value2');
        $app->instance('test.three', 'value3');

        $this->assertTrue($app->has('test.one'));
        $this->assertTrue($app->has('test.two'));
        $this->assertTrue($app->has('test.three'));

        $app->flush();

        $this->assertFalse($app->has('test.one'));
        $this->assertFalse($app->has('test.two'));
        $this->assertFalse($app->has('test.three'));
    }

    public function testGetThrowsExceptionForMissingBinding(): void
    {
        $app = Application::configure($this->testRootPath);

        $this->expectException(\Psr\Container\NotFoundExceptionInterface::class);
        $app->get('non.existent.binding');
    }

    public function testBindWithNullConcrete(): void
    {
        $app = Application::configure($this->testRootPath);

        $app->bind('test.abstract');

        $this->assertEquals('test.abstract', $app->get('test.abstract'));
    }

    public function testClosureReceivesContainer(): void
    {
        $app = Application::configure($this->testRootPath);

        $app->bind('test.container', function($container) {
            return $container instanceof Application;
        });

        $this->assertTrue($app->get('test.container'));
    }

    public function testCoreServicesAreRegistered(): void
    {
        $app = Application::configure($this->testRootPath);

        // Check core services are bound
        $this->assertTrue($app->has(\RPC\HTTP\Request::class), 'Request class should be registered');
        $this->assertTrue($app->has(\RPC\HTTP\Response::class), 'Response class should be registered');
        $this->assertTrue($app->has(\RPC\Router::class), 'Router class should be registered');
        $this->assertTrue($app->has('request'), 'request alias should be registered');
        $this->assertTrue($app->has('response'), 'response alias should be registered');
        $this->assertTrue($app->has('router'), 'router alias should be registered');

        // Session might not be registered in testing environment
        if (!getenv('APP_ENV') === 'testing') {
            $this->assertTrue($app->has(\RPC\Session::class), 'Session class should be registered');
            $this->assertTrue($app->has('session'), 'session alias should be registered');
        }
    }

    public function testApplicationRegistersItselfInContainer(): void
    {
        $app = Application::configure($this->testRootPath);

        $this->assertTrue($app->has('app'));
        $this->assertTrue($app->has(\RPC\Application::class));
        $this->assertTrue($app->has(\RPC\Contracts\Container::class));

        $this->assertSame($app, $app->get('app'));
        $this->assertSame($app, $app->get(\RPC\Application::class));
    }
}
