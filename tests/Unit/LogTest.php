<?php

namespace Tests\Unit;

use RPC\Log;
use RPC\Registry;

class LogTest extends UnitTestCase
{
    private $tempDir;
    private $originalEnv;

    protected function setUp(): void
    {
        parent::setUp();

        // Save original environment variables
        $this->originalEnv = [
            'LOGS_ENABLED' => getenv('LOGS_ENABLED'),
            'LOG_TO_FILE' => getenv('LOG_TO_FILE'),
            'LOG_PATH' => getenv('LOG_PATH'),
            'LOG_THRESHOLD' => getenv('LOG_THRESHOLD'),
            'LOG_DATE_FORMAT' => getenv('LOG_DATE_FORMAT'),
        ];

        // Create temporary directory for logs
        $this->tempDir = sys_get_temp_dir() . '/rpc_log_test_' . uniqid();
        mkdir($this->tempDir, 0750, true);

        // Set up Registry with root_path
        Registry::set('root_path', $this->tempDir);

        // Clear globals
        if (isset($GLOBALS['logs'])) {
            unset($GLOBALS['logs']);
        }
    }

    protected function tearDown(): void
    {
        // Restore environment variables
        foreach ($this->originalEnv as $key => $value) {
            if ($value === false) {
                putenv($key);
            } else {
                putenv("$key=$value");
            }
        }

        // Clean up temporary files
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    // Ensure file is writable before deletion
                    chmod($file, 0666);
                    unlink($file);
                }
            }

            // Clean up subdirectories
            $dirs = glob($this->tempDir . '/*', GLOB_ONLYDIR);
            foreach ($dirs as $dir) {
                chmod($dir, 0750);
                rmdir($dir);
            }

            @rmdir($this->tempDir);
        }

        // Clear globals
        if (isset($GLOBALS['logs'])) {
            unset($GLOBALS['logs']);
        }

        parent::tearDown();
    }

    public function testLogConstruction()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir);

        $log = new Log();

        $this->assertInstanceOf(Log::class, $log);
    }

    public function testLogDisabledByEnvironment()
    {
        putenv('LOGS_ENABLED='); // Disable logging

        $log = new Log();

        $this->assertFalse($log->_enabled);
    }

    public function testLogEnabledByEnvironment()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir);

        $log = new Log();

        $this->assertTrue($log->_enabled);
    }

    public function testLogPathFromEnvironment()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir);

        $log = new Log();

        $this->assertEquals($this->tempDir, $log->log_path);
    }

    public function testLogPathDefaultsToRootPathLogs()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH='); // No custom path

        mkdir($this->tempDir . '/logs', 0750);

        $log = new Log();

        $this->assertEquals($this->tempDir . '/logs/', $log->log_path);
    }

    public function testLogDisabledWhenDirectoryNotWritable()
    {
        putenv('LOGS_ENABLED=1');

        // Create read-only directory
        $readOnlyDir = $this->tempDir . '/readonly';
        mkdir($readOnlyDir, 0400);

        putenv('LOG_PATH=' . $readOnlyDir);

        $log = new Log();

        $this->assertFalse($log->_enabled);

        // Cleanup
        chmod($readOnlyDir, 0750);
        rmdir($readOnlyDir);
    }

    public function testLogThresholdFromEnvironment()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir);
        putenv('LOG_THRESHOLD=3');

        $log = new Log();

        $this->assertEquals(3, $log->_threshold);
    }

    public function testLogDateFormatFromEnvironment()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir);
        putenv('LOG_DATE_FORMAT=Y-m-d');

        $log = new Log();

        $this->assertEquals('Y-m-d', $log->_date_fmt);
    }

    public function testLogToFileFromEnvironment()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir);
        putenv('LOG_TO_FILE=0');

        $log = new Log();

        $this->assertFalse($log->_log_to_file);
    }

    public function testWriteLogReturnsFalseWhenDisabled()
    {
        putenv('LOGS_ENABLED=');

        $log = new Log();
        $result = $log->write_log('Test message', 'error');

        $this->assertFalse($result);
    }

    public function testWriteLogErrorLevel()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir);
        putenv('LOG_THRESHOLD=1');

        $log = new Log();
        $result = $log->write_log('Error message', 'error');

        $this->assertTrue($result);
        $this->assertArrayHasKey('logs', $GLOBALS);
        $this->assertCount(1, $GLOBALS['logs']);
        $this->assertStringContainsString('ERROR  --> Error message', $GLOBALS['logs'][0]);
    }

    public function testWriteLogDebugLevel()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir);
        putenv('LOG_THRESHOLD=2');

        $log = new Log();
        $result = $log->write_log('Debug message', 'debug');

        $this->assertTrue($result);
        $this->assertStringContainsString('DEBUG  --> Debug message', $GLOBALS['logs'][0]);
    }

    public function testWriteLogInfoLevel()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir);
        putenv('LOG_THRESHOLD=3');

        $log = new Log();
        $result = $log->write_log('Info message', 'info');

        $this->assertTrue($result);
        $this->assertStringContainsString('INFO  --> Info message', $GLOBALS['logs'][0]);
    }

    public function testWriteLogIgnoresMessageBelowThreshold()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir);
        putenv('LOG_THRESHOLD=1'); // Only ERROR

        $log = new Log();

        // DEBUG should be ignored (threshold 2 > 1)
        $result = $log->write_log('Debug message', 'debug');

        $this->assertFalse($result);
        $this->assertFalse(isset($GLOBALS['logs']));
    }

    public function testWriteLogCreatesFileWithTimestamp()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir . '/');
        putenv('LOG_TO_FILE=1');
        putenv('LOG_THRESHOLD=1');

        $log = new Log();
        $result = $log->write_log('Test message', 'error');

        $this->assertTrue($result, 'write_log should return true');

        $expectedFile = $this->tempDir . '/log-' . date('Y-m-d') . '.txt';
        $this->assertFileExists($expectedFile, "Log file should exist at: $expectedFile");

        $content = file_get_contents($expectedFile);
        $this->assertStringContainsString('ERROR', $content);
        $this->assertStringContainsString('Test message', $content);
    }

    public function testWriteLogAppendsToExistingFile()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir . '/');
        putenv('LOG_TO_FILE=1');
        putenv('LOG_THRESHOLD=3');

        $log = new Log();

        $log->write_log('First message', 'error');
        $log->write_log('Second message', 'info');

        $expectedFile = $this->tempDir . '/log-' . date('Y-m-d') . '.txt';
        $this->assertFileExists($expectedFile);

        $content = file_get_contents($expectedFile);

        $this->assertStringContainsString('First message', $content);
        $this->assertStringContainsString('Second message', $content);
    }

    public function testWriteLogOnlyToGlobalsWhenFileLoggingDisabled()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir);
        putenv('LOG_TO_FILE=0');
        putenv('LOG_THRESHOLD=1');

        $log = new Log();
        $log->write_log('Test message', 'error');

        // Should write to globals
        $this->assertArrayHasKey('logs', $GLOBALS);
        $this->assertStringContainsString('ERROR  --> Test message', $GLOBALS['logs'][0]);

        // Should NOT create file
        $expectedFile = $this->tempDir . '/log-' . date('Y-m-d') . '.txt';
        $this->assertFileDoesNotExist($expectedFile);
    }

    public function testLogLevels()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir);

        $log = new Log();

        $this->assertArrayHasKey('ERROR', $log->_levels);
        $this->assertArrayHasKey('DEBUG', $log->_levels);
        $this->assertArrayHasKey('INFO', $log->_levels);
        $this->assertArrayHasKey('ALL', $log->_levels);

        $this->assertEquals('1', $log->_levels['ERROR']);
        $this->assertEquals('2', $log->_levels['DEBUG']);
        $this->assertEquals('3', $log->_levels['INFO']);
        $this->assertEquals('4', $log->_levels['ALL']);
    }

    public function testWriteLogHandlesInvalidLevel()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir);
        putenv('LOG_THRESHOLD=3');

        $log = new Log();
        $result = $log->write_log('Test message', 'invalid_level');

        $this->assertFalse($result);
    }

    public function testWriteLogCaseInsensitiveLevel()
    {
        putenv('LOGS_ENABLED=1');
        putenv('LOG_PATH=' . $this->tempDir);
        putenv('LOG_THRESHOLD=1');

        $log = new Log();

        // Should accept lowercase
        $result = $log->write_log('Test message', 'error');
        $this->assertTrue($result);

        // Should convert to uppercase
        $this->assertStringContainsString('ERROR', $GLOBALS['logs'][0]);
    }
}
