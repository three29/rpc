<?php

namespace Tests\Unit\Exception;

use RPC\Exception\Handler;
use RPC\Exception\HttpException;
use RPC\Exception\MethodNotAllowedException;
use RPC\Exception\RouteNotFoundException;
use RPC\Exception\TokenMismatchException;
use Tests\Unit\UnitTestCase;

class HandlerTest extends UnitTestCase
{
    private string $tmpDir;
    private array $originalServer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalServer = $_SERVER;
        unset($_SERVER['HTTP_ACCEPT'], $_SERVER['CONTENT_TYPE'], $_SERVER['HTTP_X_REQUESTED_WITH']);

        $this->tmpDir = sys_get_temp_dir() . '/rpc_handler_test_' . uniqid();
        mkdir($this->tmpDir . '/View/errors', 0777, true);
        mkdir($this->tmpDir . '/cache', 0777, true);
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        Handler::flushReporters();
        Handler::$output_buffer_level = null;
        $this->removeDirectory($this->tmpDir);

        parent::tearDown();
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function handler(): LoggingHandler
    {
        return new LoggingHandler($this->tmpDir . '/View', $this->tmpDir . '/cache');
    }

    public function testStatusCodes(): void
    {
        $handler = $this->handler();

        $this->assertSame(500, $handler->getStatusCode(new \RuntimeException('x')));
        $this->assertSame(500, $handler->getStatusCode(new \TypeError('x')));
        $this->assertSame(404, $handler->getStatusCode(new RouteNotFoundException('x')));
        $this->assertSame(405, $handler->getStatusCode(new MethodNotAllowedException(['POST'])));
        $this->assertSame(419, $handler->getStatusCode(new TokenMismatchException('x')));
        $this->assertSame(410, $handler->getStatusCode(new HttpException('x', 410)));
        $this->assertSame(500, $handler->getStatusCode(new HttpException('x')));
    }

    public function testPlainTextFallbackMatchesPreviousOutput(): void
    {
        $handler = new Handler(null, null);

        $this->assertSame('404 - Page Not Found', $handler->prepareResponse(new RouteNotFoundException('x'))['body']);
        $this->assertSame('500 - Internal Server Error', $handler->prepareResponse(new \RuntimeException('x'))['body']);
    }

    public function testRendersMostSpecificTemplate(): void
    {
        file_put_contents($this->tmpDir . '/View/errors/4xx.php', 'client <?= $status_code ?> <?= $status_text ?>');
        file_put_contents($this->tmpDir . '/View/errors/404.php', 'missing page');

        $handler = $this->handler();

        $this->assertSame('missing page', $handler->prepareResponse(new RouteNotFoundException('x'))['body']);
        $this->assertSame('client 405 Method Not Allowed', $handler->prepareResponse(new MethodNotAllowedException(['GET']))['body']);
        // No 5xx template: plain text
        $this->assertSame('500 - Internal Server Error', $handler->prepareResponse(new \RuntimeException('x'))['body']);
    }

    public function testBrokenTemplateFallsBackToPlainText(): void
    {
        file_put_contents($this->tmpDir . '/View/errors/500.php', 'half <?php throw new \RuntimeException("template bug"); ?>');

        $level = ob_get_level();
        $response = $this->handler()->prepareResponse(new \RuntimeException('x'));

        $this->assertSame('500 - Internal Server Error', $response['body']);
        $this->assertSame($level, ob_get_level());
    }

    public function testJsonResponseForJsonRequests(): void
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json';

        $response = $this->handler()->prepareResponse(new RouteNotFoundException('internal detail'));

        $this->assertSame(404, $response['status']);
        $this->assertSame('application/json', $response['headers']['Content-Type']);
        $this->assertSame(['error' => 1, 'error_message' => 'Page Not Found', 'data' => []], json_decode($response['body'], true));
        $this->assertStringNotContainsString('internal detail', $response['body']);
    }

    public function testAjaxRequestsGetJson(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        $this->assertSame('application/json', $this->handler()->prepareResponse(new \RuntimeException('x'))['headers']['Content-Type']);
    }

    public function testExceptionHeadersArePassedThrough(): void
    {
        $response = $this->handler()->prepareResponse(new MethodNotAllowedException(['GET', 'HEAD']));

        $this->assertSame(['Allow' => 'GET, HEAD'], $response['headers']);
    }

    public function testMessagesAreNotExposedInResponse(): void
    {
        $response = $this->handler()->prepareResponse(new \RuntimeException('SQLSTATE secret detail'));

        $this->assertStringNotContainsString('secret', $response['body']);
    }

    public function testServerErrorsAreLoggedAndSentToReporters(): void
    {
        $reported = [];
        Handler::reportUsing(function (\Throwable $e) use (&$reported) {
            $reported[] = $e;
        });

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/cart/add';

        $handler = $this->handler();
        $e = new \RuntimeException('boom');
        $handler->report($e);

        $this->assertSame([$e], $reported);
        $this->assertCount(1, $handler->logged);
        $this->assertStringContainsString('[RPC] RuntimeException: boom in ' . __FILE__, $handler->logged[0]);
        $this->assertStringContainsString('[POST /cart/add]', $handler->logged[0]);
        $this->assertStringContainsString('Stack trace:', $handler->logged[0]);
    }

    public function testServerErrorsAreSentToSentryWhenInstalled(): void
    {
        require_once __DIR__ . '/../../Fixtures/sentry_stub.php';
        $GLOBALS['__sentry_captured'] = [];

        $e = new \RuntimeException('boom');
        $this->handler()->report($e);
        $this->handler()->report(new RouteNotFoundException('x'));

        $this->assertSame([$e], $GLOBALS['__sentry_captured']);
        unset($GLOBALS['__sentry_captured']);
    }

    public function testClientErrorsAreNotReported(): void
    {
        $reported = [];
        Handler::reportUsing(function (\Throwable $e) use (&$reported) {
            $reported[] = $e;
        });

        $handler = $this->handler();
        $handler->report(new RouteNotFoundException('x'));
        $handler->report(new TokenMismatchException('x'));

        $this->assertSame([], $reported);
        $this->assertSame([], $handler->logged);
    }

    public function testFailingReporterDoesNotBreakReporting(): void
    {
        Handler::reportUsing(function () {
            throw new \LogicException('reporter down');
        });

        $handler = $this->handler();
        $handler->report(new \RuntimeException('boom'));

        $this->assertCount(1, $handler->logged);
    }

    public function testLoggedTraceOmitsArguments(): void
    {
        $e = (function (string $password) {
            return new \RuntimeException('connect failed');
        })('hunter2-secret');

        $log = $this->handler()->formatForLog($e);

        $this->assertStringNotContainsString('hunter2-secret', $log);
        $this->assertStringContainsString('{main}', $log);
    }

    public function testLogIncludesPreviousExceptions(): void
    {
        $e = new \RuntimeException('outer', 0, new \LogicException('inner cause'));

        $this->assertStringContainsString('Caused by LogicException: inner cause', $this->handler()->formatForLog($e));
    }

    public function testRenderDiscardsBufferedResponseOutput(): void
    {
        ob_start();
        Handler::$output_buffer_level = ob_get_level();
        ob_start();
        echo 'partial page';

        (new Handler(null, null))->render(new \RuntimeException('x'));

        $this->assertSame(Handler::$output_buffer_level, ob_get_level());
        $this->assertSame('500 - Internal Server Error', ob_get_clean());
    }

    public function testRenderLeavesUnrelatedBuffersAlone(): void
    {
        ob_start();
        echo 'kept ';

        (new Handler(null, null))->render(new RouteNotFoundException('x'));

        $this->assertSame('kept 404 - Page Not Found', ob_get_clean());
    }
}

class LoggingHandler extends Handler
{
    /** @var string[] */
    public array $logged = [];

    protected function log(string $message): void
    {
        $this->logged[] = $message;
    }
}
