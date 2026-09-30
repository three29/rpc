<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Reset singleton instances between tests
        if (isset($GLOBALS['_RPC_'])) {
            unset($GLOBALS['_RPC_']);
        }
    }

    protected function tearDown(): void
    {
        // Pop the handler RPC\Bootstraps\Errors::handle() installs, so tests
        // leave the global exception handler stack as they found it
        $current = set_exception_handler(null);
        restore_exception_handler();
        if ($current === [\RPC\Bootstraps\Errors::class, 'handleUncaught']) {
            restore_exception_handler();
        }

        parent::tearDown();

        // Clean up after tests
        if (isset($GLOBALS['_RPC_'])) {
            unset($GLOBALS['_RPC_']);
        }
    }
}
