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
}
