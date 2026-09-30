<?php

namespace Tests\Unit\Router;

use RPC\Contracts\ExceptionHandler;
use RPC\Exception\HttpException;
use RPC\Exception\MethodNotAllowedException;
use RPC\Exception\RouteNotFoundException;
use RPC\Exception\RoutingException;
use RPC\Router;
use Tests\Unit\UnitTestCase;

require_once __DIR__ . '/../../Fixtures/Controller/Widgets.php';

class DispatchTest extends UnitTestCase
{
    private RecordingExceptionHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_POST = [];
        $_GET = [];

        $this->handler = new RecordingExceptionHandler();
    }

    private function dispatch(string $method, string $uri): string
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;

        $router = new Router();
        $router->setExceptionHandler($this->handler);

        ob_start();
        $router->run();

        return (string) ob_get_clean();
    }

    public function testDispatchesMatchingMethodHandler(): void
    {
        $this->assertSame('widgets:save:post', $this->dispatch('POST', '/widgets/save'));
        $this->assertSame([], $this->handler->rendered);
    }

    public function testHeadIsDispatchedToGetHandler(): void
    {
        $this->assertSame('widgets:save:get', $this->dispatch('HEAD', '/widgets/save'));
        $this->assertSame([], $this->handler->rendered);
    }

    public function testDeleteHandlerIsDispatched(): void
    {
        $this->assertSame('widgets:remove:delete', $this->dispatch('DELETE', '/widgets/remove'));
    }

    public function testUnknownControllerIsRouteNotFound(): void
    {
        $this->dispatch('GET', '/no-such-controller');

        $this->assertCount(1, $this->handler->rendered);
        $this->assertInstanceOf(RouteNotFoundException::class, $this->handler->rendered[0]);
        $this->assertSame(404, $this->handler->rendered[0]->getStatusCode());
    }

    public function testUnknownActionIsRouteNotFound(): void
    {
        $this->dispatch('GET', '/widgets/nosuchaction');

        $this->assertInstanceOf(RouteNotFoundException::class, $this->handler->rendered[0]);
        // Still a RoutingException, so existing catch blocks keep working
        $this->assertInstanceOf(RoutingException::class, $this->handler->rendered[0]);
    }

    public function testWrongMethodIsMethodNotAllowedWithAllowHeader(): void
    {
        $this->dispatch('GET', '/widgets/submit');

        $e = $this->handler->rendered[0];
        $this->assertInstanceOf(MethodNotAllowedException::class, $e);
        $this->assertSame(405, $e->getStatusCode());
        $this->assertSame(['Allow' => 'POST'], $e->getHeaders());
    }

    public function testAllowHeaderIncludesHeadWhenGetExists(): void
    {
        $this->dispatch('PUT', '/widgets/save');

        $this->assertSame(['Allow' => 'GET, HEAD, POST'], $this->handler->rendered[0]->getHeaders());
    }

    public function testUndispatchableMethodIsMethodNotAllowed(): void
    {
        $this->dispatch('OPTIONS', '/widgets/save');

        $this->assertInstanceOf(MethodNotAllowedException::class, $this->handler->rendered[0]);
    }

    public function testExceptionInActionIsReportedAndRendered(): void
    {
        $this->dispatch('GET', '/widgets/explode');

        $this->assertCount(1, $this->handler->reported);
        $this->assertSame('boom', $this->handler->reported[0]->getMessage());
        $this->assertSame($this->handler->reported, $this->handler->rendered);
    }

    public function testPhpErrorInActionIsHandled(): void
    {
        $this->dispatch('GET', '/widgets/typeError');

        $this->assertInstanceOf(\TypeError::class, $this->handler->rendered[0]);
    }

    public function testHttpExceptionFromActionIsHandled(): void
    {
        $this->dispatch('GET', '/widgets/gone');

        $this->assertInstanceOf(HttpException::class, $this->handler->rendered[0]);
        $this->assertSame(410, $this->handler->rendered[0]->getStatusCode());
    }

    public function testServerErrorsAreRethrownWhenShowErrorsIsOn(): void
    {
        $_ENV['SHOW_ERRORS'] = 'true';

        try {
            $this->expectException(\RuntimeException::class);
            $this->dispatch('GET', '/widgets/explode');
        } finally {
            unset($_ENV['SHOW_ERRORS']);
            // dispatch() never reached ob_get_clean()
            ob_end_clean();
        }
    }

    public function testClientErrorsAreRenderedEvenWhenShowErrorsIsOn(): void
    {
        $_ENV['SHOW_ERRORS'] = 'true';

        try {
            $this->dispatch('GET', '/widgets/nosuchaction');
        } finally {
            unset($_ENV['SHOW_ERRORS']);
        }

        $this->assertInstanceOf(RouteNotFoundException::class, $this->handler->rendered[0]);
    }
}

class RecordingExceptionHandler implements ExceptionHandler
{
    /** @var \Throwable[] */
    public array $reported = [];

    /** @var \Throwable[] */
    public array $rendered = [];

    public function report(\Throwable $e): void
    {
        $this->reported[] = $e;
    }

    public function render(\Throwable $e): void
    {
        $this->rendered[] = $e;
    }
}
