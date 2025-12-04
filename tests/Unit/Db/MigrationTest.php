<?php

namespace Tests\Unit\Db;

use RPC\Db\Migration;
use RPC\Exception\ConfigurationException;
use Tests\Unit\UnitTestCase;

class MigrationTest extends UnitTestCase
{
    public function testMigrationClassExists()
    {
        $this->assertTrue(class_exists(Migration::class));
    }

    public function testMigrationFilePatternMatching()
    {
        // Test the regex pattern used in Migration::run()
        $pattern = '/^(.*)?_([0-9]+)\.php/';

        $validFiles = [
            'create_users_001.php' => true,
            'add_email_to_users_002.php' => true,
            'migration_123.php' => true,
            '_001.php' => true,
        ];

        $invalidFiles = [
            'migration.php' => false,
            'test.txt' => false,
            '001.sql' => false,
            'migration_abc.php' => false,
        ];

        foreach ($validFiles as $file => $expected) {
            $result = preg_match($pattern, $file, $matches);
            $this->assertEquals($expected ? 1 : 0, $result, "File: $file");

            if ($expected) {
                $this->assertArrayHasKey(2, $matches, "File $file should have migration number");
                $this->assertIsNumeric($matches[2], "Migration number should be numeric");
            }
        }

        foreach ($invalidFiles as $file => $expected) {
            $result = preg_match($pattern, $file, $matches);
            $this->assertEquals(0, $result, "File: $file should not match");
        }
    }

    public function testMigrationNumberExtraction()
    {
        $pattern = '/^(.*)?_([0-9]+)\.php/';

        $testCases = [
            'create_users_001.php' => '001',
            'add_email_042.php' => '042',
            'migration_999.php' => '999',
        ];

        foreach ($testCases as $filename => $expectedNumber) {
            preg_match($pattern, $filename, $matches);
            $this->assertEquals($expectedNumber, $matches[2], "Failed for $filename");
        }
    }
}
