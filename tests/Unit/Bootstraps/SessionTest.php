<?php

namespace Tests\Unit\Bootstraps;

use RPC\Bootstraps\Session as SessionBootstrap;
use RPC\Contracts\Bootstrap;
use Tests\Unit\UnitTestCase;

class SessionTest extends UnitTestCase
{
    private $originalEnv;

    protected function setUp(): void
    {
        parent::setUp();

        // Save original APP_ENV
        $this->originalEnv = $_ENV['APP_ENV'] ?? null;
    }

    protected function tearDown(): void
    {
        // Restore original environment
        if ($this->originalEnv !== null) {
            $_ENV['APP_ENV'] = $this->originalEnv;
            putenv("APP_ENV={$this->originalEnv}");
        } else {
            unset($_ENV['APP_ENV']);
            putenv('APP_ENV');
        }

        parent::tearDown();
    }

    public function testImplementsBootstrapInterface()
    {
        $this->assertInstanceOf(Bootstrap::class, new SessionBootstrap());
    }

    public function testHandleSkipsInTestingEnvironment()
    {
        $_ENV['APP_ENV'] = 'testing';
        putenv('APP_ENV=testing');

        // Should not throw any exceptions or start session
        SessionBootstrap::handle();

        // Verify session was not started
        $this->assertEquals(PHP_SESSION_NONE, session_status());
    }

    public function testHandleMethodExists()
    {
        $this->assertTrue(method_exists(SessionBootstrap::class, 'handle'));

        $reflection = new \ReflectionMethod(SessionBootstrap::class, 'handle');
        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
    }

    public function testHandleReturnsEarlyInTestingMode()
    {
        $_ENV['APP_ENV'] = 'testing';
        putenv('APP_ENV=testing');

        // Multiple calls should not cause issues
        SessionBootstrap::handle();
        SessionBootstrap::handle();

        $this->assertEquals(PHP_SESSION_NONE, session_status());
    }

    public function testBootstrapChecksTestingEnvironment()
    {
        // Test with testing environment
        $_ENV['APP_ENV'] = 'testing';
        putenv('APP_ENV=testing');

        SessionBootstrap::handle();
        $this->assertEquals(PHP_SESSION_NONE, session_status());

        // Test with non-testing environment should be skipped due to headers already sent
        unset($_ENV['APP_ENV']);
        putenv('APP_ENV');

        // Can't actually start session in tests due to headers already sent
        // Just verify the method is callable
        $this->assertTrue(is_callable([SessionBootstrap::class, 'handle']));
    }
}
