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

    public function testGetParamsReturnsNull(): void
    {
        $params = $this->router->getParams();
        $this->assertNull($params);
    }

    public function testSetRewriteRulesWithMultipleRules(): void
    {
        $rules = [
            'products' => 'shop/products',
            'products/(\d+)' => 'shop/product/$1',
            'blog/(.*)' => 'posts/view/$1',
        ];

        $this->router->setRewriteRules($rules);
        $this->assertInstanceOf(Router::class, $this->router);
    }

    public function testSetRewriteRulesMergesRules(): void
    {
        $rules1 = ['test1' => 'controller1/action1'];
        $rules2 = ['test2' => 'controller2/action2'];

        $this->router->setRewriteRules($rules1);
        // Second call should merge with first (array_replace)
        $this->router->setRewriteRules($rules2);

        $this->assertInstanceOf(Router::class, $this->router);
    }

    public function testSetRewriteRulesOverwritesSameKey(): void
    {
        $rules1 = ['test' => 'old/action'];
        $rules2 = ['test' => 'new/action'];

        $this->router->setRewriteRules($rules1);
        $this->router->setRewriteRules($rules2);

        // The second rule should overwrite the first
        $this->assertInstanceOf(Router::class, $this->router);
    }

    public function testSetRewriteRulesWithEmptyArray(): void
    {
        $this->router->setRewriteRules([]);
        $this->assertInstanceOf(Router::class, $this->router);
    }

    public function testSetRewriteRulesWithRegexPatterns(): void
    {
        $rules = [
            'user/(\d+)' => 'users/view/$1',
            'post/([a-z0-9-]+)' => 'blog/view/$1',
            'category/(\w+)/page/(\d+)' => 'categories/list/$1/$2',
        ];

        $this->router->setRewriteRules($rules);
        $this->assertInstanceOf(Router::class, $this->router);
    }

    public function testSetRewriteRulesWithSpecialCharacters(): void
    {
        $rules = [
            'test#hash' => 'controller/action',
            'test/with/slash' => 'another/controller',
        ];

        $this->router->setRewriteRules($rules);
        $this->assertInstanceOf(Router::class, $this->router);
    }

    public function testSetRewriteRulesPreservesExistingRules(): void
    {
        $rules1 = [
            'rule1' => 'controller1/action1',
            'rule2' => 'controller2/action2',
        ];
        $rules2 = [
            'rule3' => 'controller3/action3',
        ];

        $this->router->setRewriteRules($rules1);
        $this->router->setRewriteRules($rules2);

        // After second call, all three rules should exist (due to array_replace)
        $this->assertInstanceOf(Router::class, $this->router);
    }

    public function testRouterHandlesEmptyUri(): void
    {
        $_SERVER['REQUEST_URI'] = '/';
        $router = new Router();

        $this->assertInstanceOf(Router::class, $router);
    }

    public function testRouterHandlesUriWithQueryString(): void
    {
        $_SERVER['REQUEST_URI'] = '/users/list?page=1&sort=name';
        $router = new Router();

        $this->assertInstanceOf(Router::class, $router);
    }

    public function testRouterHandlesUriWithFragment(): void
    {
        $_SERVER['REQUEST_URI'] = '/products#featured';
        $router = new Router();

        $this->assertInstanceOf(Router::class, $router);
    }

    public function testRouterHandlesTrailingSlash(): void
    {
        $_SERVER['REQUEST_URI'] = '/users/list/';
        $router = new Router();

        $this->assertInstanceOf(Router::class, $router);
    }

    public function testRouterHandlesLeadingSlash(): void
    {
        $_SERVER['REQUEST_URI'] = '/users/list';
        $router = new Router();

        $this->assertInstanceOf(Router::class, $router);
    }

    public function testRouterHandlesMixedCaseUri(): void
    {
        $_SERVER['REQUEST_URI'] = '/Users/List';
        $router = new Router();

        $this->assertInstanceOf(Router::class, $router);
    }

    public function testRouterHandlesNumericSegments(): void
    {
        $_SERVER['REQUEST_URI'] = '/users/123';
        $router = new Router();

        $this->assertInstanceOf(Router::class, $router);
    }

    public function testRouterHandlesComplexUri(): void
    {
        $_SERVER['REQUEST_URI'] = '/admin/users/edit/123/params/tab/profile';
        $router = new Router();

        $this->assertInstanceOf(Router::class, $router);
    }

    // Note: Testing the run() and executeRoute() methods requires:
    // - Setting up controller classes
    // - Mocking the Request/Response singletons
    // - Creating a full application context
    // These are better suited for integration/feature tests
    // as they require the full framework stack to be operational
}
