<?php

namespace Tests\Unit;

use RPC\Application;
use RPC\HTTP\Request;
use RPC\HTTP\Response;
use RPC\Session;

class HelpersTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure helpers are loaded
        require_once __DIR__ . '/../../src/RPC/helpers.php';
    }

    public function testAppFunctionExists()
    {
        $this->assertTrue(function_exists('app'));
    }

    public function testRequestFunctionExists()
    {
        $this->assertTrue(function_exists('request'));
    }

    public function testResponseFunctionExists()
    {
        $this->assertTrue(function_exists('response'));
    }

    public function testSessionFunctionExists()
    {
        $this->assertTrue(function_exists('session'));
    }

    public function testEventsFunctionExists()
    {
        $this->assertTrue(function_exists('events'));
    }

    public function testDispatchFunctionExists()
    {
        $this->assertTrue(function_exists('dispatch'));
    }

    public function testAppFunctionReturnsApplication()
    {
        // Ensure Application is configured
        if (!Application::$app) {
            Application::configure(__DIR__ . '/../..');
        }

        $app = app();

        $this->assertInstanceOf(Application::class, $app);
    }

    public function testAppFunctionResolves()
    {
        // Ensure Application is configured
        if (!Application::$app) {
            Application::configure(__DIR__ . '/../..');
        }

        // Bind a test value
        app()->bind('test.value', fn() => 'test-result');

        $result = app('test.value');

        $this->assertEquals('test-result', $result);
    }

    public function testAppFunctionReturnsDefault()
    {
        // Ensure Application is configured
        if (!Application::$app) {
            Application::configure(__DIR__ . '/../..');
        }

        $result = app('nonexistent.service', 'default-value');

        $this->assertEquals('default-value', $result);
    }

    public function testRequestFunctionReturnsRequest()
    {
        // Ensure Application is configured
        if (!Application::$app) {
            Application::configure(__DIR__ . '/../..');
        }

        $request = request();

        $this->assertInstanceOf(Request::class, $request);
    }

    public function testResponseFunctionReturnsResponse()
    {
        // Ensure Application is configured
        if (!Application::$app) {
            Application::configure(__DIR__ . '/../..');
        }

        $response = response();

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testSessionFunctionReturnsSession()
    {
        // Ensure Application is configured
        if (!Application::$app) {
            Application::configure(__DIR__ . '/../..');
        }

        $session = session();

        $this->assertInstanceOf(Session::class, $session);
    }

    public function testEventsFunctionReturnsEventDispatcher()
    {
        // Ensure Application is configured
        if (!Application::$app) {
            Application::configure(__DIR__ . '/../..');
        }

        $events = events();

        $this->assertInstanceOf(\Psr\EventDispatcher\EventDispatcherInterface::class, $events);
    }

    public function testDispatchFunctionDispatchesEvent()
    {
        // Ensure Application is configured
        if (!Application::$app) {
            Application::configure(__DIR__ . '/../..');
        }

        $event = new class {
            public $dispatched = false;
        };

        $result = dispatch($event);

        $this->assertSame($event, $result);
    }

    public function testEnvFunctionExists()
    {
        $this->assertTrue(function_exists('env'));
    }

    public function testEnvReturnsValueFromEnv()
    {
        $_ENV['TEST_VAR'] = 'test_value';

        $this->assertEquals('test_value', env('TEST_VAR'));

        unset($_ENV['TEST_VAR']);
    }

    public function testEnvReturnsValueFromServer()
    {
        $_SERVER['TEST_SERVER_VAR'] = 'server_value';

        $this->assertEquals('server_value', env('TEST_SERVER_VAR'));

        unset($_SERVER['TEST_SERVER_VAR']);
    }

    public function testEnvPrefersEnvOverServer()
    {
        $_ENV['TEST_PRIORITY'] = 'env_value';
        $_SERVER['TEST_PRIORITY'] = 'server_value';

        $this->assertEquals('env_value', env('TEST_PRIORITY'));

        unset($_ENV['TEST_PRIORITY']);
        unset($_SERVER['TEST_PRIORITY']);
    }

    public function testEnvReturnsDefaultWhenNotFound()
    {
        $this->assertEquals('default_value', env('NONEXISTENT_VAR', 'default_value'));
        $this->assertNull(env('NONEXISTENT_VAR'));
    }

    public function testEnvConvertsTrueString()
    {
        $_ENV['TEST_TRUE'] = 'true';
        $_ENV['TEST_TRUE_PAREN'] = '(true)';

        $this->assertTrue(env('TEST_TRUE'));
        $this->assertTrue(env('TEST_TRUE_PAREN'));

        unset($_ENV['TEST_TRUE']);
        unset($_ENV['TEST_TRUE_PAREN']);
    }

    public function testEnvConvertsFalseString()
    {
        $_ENV['TEST_FALSE'] = 'false';
        $_ENV['TEST_FALSE_PAREN'] = '(false)';

        $this->assertFalse(env('TEST_FALSE'));
        $this->assertFalse(env('TEST_FALSE_PAREN'));

        unset($_ENV['TEST_FALSE']);
        unset($_ENV['TEST_FALSE_PAREN']);
    }

    public function testEnvConvertsNullString()
    {
        $_ENV['TEST_NULL'] = 'null';
        $_ENV['TEST_NULL_PAREN'] = '(null)';

        $this->assertNull(env('TEST_NULL'));
        $this->assertNull(env('TEST_NULL_PAREN'));

        unset($_ENV['TEST_NULL']);
        unset($_ENV['TEST_NULL_PAREN']);
    }

    public function testEnvConvertsEmptyString()
    {
        $_ENV['TEST_EMPTY'] = 'empty';
        $_ENV['TEST_EMPTY_PAREN'] = '(empty)';

        $this->assertEquals('', env('TEST_EMPTY'));
        $this->assertEquals('', env('TEST_EMPTY_PAREN'));

        unset($_ENV['TEST_EMPTY']);
        unset($_ENV['TEST_EMPTY_PAREN']);
    }

    public function testEnvConversionIsCaseInsensitive()
    {
        $_ENV['TEST_CASE_TRUE'] = 'TRUE';
        $_ENV['TEST_CASE_FALSE'] = 'FALSE';
        $_ENV['TEST_CASE_NULL'] = 'NULL';

        $this->assertTrue(env('TEST_CASE_TRUE'));
        $this->assertFalse(env('TEST_CASE_FALSE'));
        $this->assertNull(env('TEST_CASE_NULL'));

        unset($_ENV['TEST_CASE_TRUE']);
        unset($_ENV['TEST_CASE_FALSE']);
        unset($_ENV['TEST_CASE_NULL']);
    }

    public function testEnvDoesNotConvertNumericStrings()
    {
        $_ENV['TEST_NUMBER'] = '123';

        $this->assertEquals('123', env('TEST_NUMBER'));
        $this->assertIsString(env('TEST_NUMBER'));

        unset($_ENV['TEST_NUMBER']);
    }
}
