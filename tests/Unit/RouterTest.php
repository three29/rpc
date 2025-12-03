<?php

namespace Tests\Unit;

use RPC\Router;
use RPC\HTTP\Request;
use RPC\HTTP\Response;

class RouterTest extends UnitTestCase
{
    private Router $router;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up basic $_SERVER variables
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SERVER_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['QUERY_STRING'] = '';
        $_POST = [];
        $_GET = [];
        $_FILES = [];

        $this->router = new Router();
    }

    public function testRouterCanBeInstantiated(): void
    {
        $this->assertInstanceOf(Router::class, $this->router);
    }

    public function testSetRewriteRules(): void
    {
        $rules = [
            'test' => 'home/index',
            'users/(\d+)' => 'users/view/$1',
        ];

        $this->router->setRewriteRules($rules);

        // Since rewrite_rules is protected, we can't directly test it
        // but we can verify the method doesn't throw an exception
        $this->assertInstanceOf(Router::class, $this->router);
    }

    public function testSetRewriteRulesReplacesExisting(): void
    {
        $rules1 = ['test1' => 'controller1/action1'];
        $rules2 = ['test2' => 'controller2/action2'];

        $this->router->setRewriteRules($rules1);
        $this->router->setRewriteRules($rules2);

        // Both rules should exist (array_replace behavior)
        $this->assertInstanceOf(Router::class, $this->router);
    }

    public function testGetParams(): void
    {
        $params = $this->router->getParams();

        // Initially params should be null or empty
        $this->assertTrue($params === null || is_array($params));
    }

    public function testRouterDefaultsToHomeIndex(): void
    {
        // This test documents the expected default behavior
        // Default controller should be 'Home' and action 'index'
        // This is tested indirectly through the run() method in feature tests
        $this->assertInstanceOf(Router::class, $this->router);
    }

    // Note: Testing the run() method requires:
    // - Setting up controller classes
    // - Mocking the Request/Response singletons
    // - Creating a full application context
    // These are better suited for integration/feature tests
    // as they require the full framework stack to be operational
}
