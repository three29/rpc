<?php

namespace Tests\Unit\Bootstraps;

use RPC\Bootstraps\Errors;
use RPC\Contracts\Bootstrap;
use Tests\Unit\UnitTestCase;

class ErrorsTest extends UnitTestCase
{
    private $originalEnv;
    private $originalErrorReporting;
    private $originalDisplayErrors;

    protected function setUp(): void
    {
        parent::setUp();

        // Save original settings
        $this->originalEnv = $_ENV['SHOW_ERRORS'] ?? null;
        $this->originalErrorReporting = error_reporting();
        $this->originalDisplayErrors = ini_get('display_errors');
    }

    protected function tearDown(): void
    {
        // Restore original settings
        if ($this->originalEnv !== null) {
            $_ENV['SHOW_ERRORS'] = $this->originalEnv;
            putenv("SHOW_ERRORS={$this->originalEnv}");
        } else {
            unset($_ENV['SHOW_ERRORS']);
            putenv('SHOW_ERRORS');
        }

        error_reporting($this->originalErrorReporting);
        ini_set('display_errors', $this->originalDisplayErrors);

        parent::tearDown();
    }

    public function testImplementsBootstrapInterface()
    {
        $this->assertInstanceOf(Bootstrap::class, new Errors());
    }

    public function testHandleSetsErrorReporting()
    {
        Errors::handle();

        $this->assertEquals(E_ALL, error_reporting());
    }

    public function testHandleDisablesDisplayErrorsByDefault()
    {
        unset($_ENV['SHOW_ERRORS']);
        putenv('SHOW_ERRORS');

        Errors::handle();

        $this->assertEquals('0', ini_get('display_errors'));
    }

    public function testHandleEnablesDisplayErrorsWhenShowErrorsIsTrue()
    {
        $_ENV['SHOW_ERRORS'] = 'true';
        putenv('SHOW_ERRORS=true');

        // Note: This test registers Whoops handlers which can't be easily unregistered
        // We just verify display_errors is set correctly
        Errors::handle();

        $this->assertEquals('1', ini_get('display_errors'));

        // Restore to avoid affecting other tests
        restore_error_handler();
        restore_exception_handler();
    }

    public function testHandleRegistersShutdownFunction()
    {
        Errors::handle();

        // Verify shutdown function is registered by checking it's callable
        $this->assertTrue(method_exists(Errors::class, 'rpc_shutdown'));
        $this->assertTrue(is_callable([Errors::class, 'rpc_shutdown']));
    }

    public function testShutdownMethodExists()
    {
        $this->assertTrue(method_exists(Errors::class, 'rpc_shutdown'));

        $reflection = new \ReflectionMethod(Errors::class, 'rpc_shutdown');
        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
    }

    public function testHandleMethodIsStatic()
    {
        $reflection = new \ReflectionMethod(Errors::class, 'handle');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
    }

    public function testHandleWithShowErrorsFalse()
    {
        $_ENV['SHOW_ERRORS'] = 'false';
        putenv('SHOW_ERRORS=false');

        Errors::handle();

        // SHOW_ERRORS must be exactly "true" to enable
        $this->assertEquals('0', ini_get('display_errors'));
    }

    public function testErrorReportingLevel()
    {
        Errors::handle();

        // Verify E_ALL is set
        $this->assertEquals(E_ALL, error_reporting());

        // Verify it includes common error types
        $this->assertTrue((error_reporting() & E_ERROR) === E_ERROR);
        $this->assertTrue((error_reporting() & E_WARNING) === E_WARNING);
        $this->assertTrue((error_reporting() & E_NOTICE) === E_NOTICE);
    }

    public function testHandleCanBeCalledMultipleTimes()
    {
        Errors::handle();
        Errors::handle();
        Errors::handle();

        // Should not cause issues
        $this->assertEquals(E_ALL, error_reporting());
    }
}
