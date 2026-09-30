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
        parent::tearDown();

        // Clean up after tests
        if (isset($GLOBALS['_RPC_'])) {
            unset($GLOBALS['_RPC_']);
        }
    }
}
