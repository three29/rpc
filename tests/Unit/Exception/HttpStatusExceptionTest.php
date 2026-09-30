<?php

namespace Tests\Unit\Exception;

use RPC\Exception\HttpException;
use RPC\Exception\HttpExceptionInterface;
use RPC\Exception\MethodNotAllowedException;
use RPC\Exception\RouteNotFoundException;
use RPC\Exception\RoutingException;
use RPC\Exception\RuntimeException;
use RPC\Exception\SecurityException;
use RPC\Exception\TokenMismatchException;
use Tests\Unit\UnitTestCase;

class HttpStatusExceptionTest extends UnitTestCase
{
    public function testHttpExceptionUsesCodeAsStatus(): void
    {
        $e = new HttpException('Gone', 410);

        $this->assertInstanceOf(HttpExceptionInterface::class, $e);
        $this->assertInstanceOf(RuntimeException::class, $e);
        $this->assertSame(410, $e->getStatusCode());
        $this->assertSame(410, $e->getCode());
    }

    public function testHttpExceptionDefaultsToServerError(): void
    {
        $this->assertSame(500, (new HttpException('x'))->getStatusCode());
        $this->assertSame(500, (new HttpException('x', 200))->getStatusCode());
        $this->assertSame(500, (new HttpException('x', 42))->getStatusCode());
    }

    public function testHttpExceptionHeaders(): void
    {
        $e = (new HttpException('Slow down', 429))->setHeaders(['Retry-After' => '60']);

        $this->assertSame(['Retry-After' => '60'], $e->getHeaders());
    }

    public function testRouteNotFound(): void
    {
        $e = new RouteNotFoundException('missing');

        $this->assertInstanceOf(RoutingException::class, $e);
        $this->assertSame(404, $e->getStatusCode());
        $this->assertSame([], $e->getHeaders());
    }

    public function testMethodNotAllowed(): void
    {
        $e = new MethodNotAllowedException(['get', 'POST', 'GET'], 'nope');

        $this->assertInstanceOf(RoutingException::class, $e);
        $this->assertSame(405, $e->getStatusCode());
        $this->assertSame(['GET', 'POST'], $e->getAllowedMethods());
        $this->assertSame(['Allow' => 'GET, POST'], $e->getHeaders());
        $this->assertSame('nope', $e->getMessage());
    }

    public function testTokenMismatch(): void
    {
        $e = new TokenMismatchException('bad token');

        $this->assertInstanceOf(SecurityException::class, $e);
        $this->assertSame(419, $e->getStatusCode());
    }
}
