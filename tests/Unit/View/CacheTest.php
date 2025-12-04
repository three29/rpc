<?php

namespace Tests\Unit\View;

use RPC\View\Cache;
use Tests\Unit\UnitTestCase;

class CacheTest extends UnitTestCase
{
    private $tempDir;
    private $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Create temporary directory for cache testing
        $this->tempDir = sys_get_temp_dir() . '/rpc_cache_test_' . uniqid();
        $this->cacheDir = $this->tempDir . '/cache';
    }

    protected function tearDown(): void
    {
        // Clean up temporary files
        if (is_dir($this->tempDir)) {
            $this->recursiveDelete($this->tempDir);
        }

        parent::tearDown();
    }

    private function recursiveDelete($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->recursiveDelete($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testCacheConstruction()
    {
        $cache = new Cache($this->cacheDir);

        $this->assertInstanceOf(Cache::class, $cache);
        $this->assertTrue(is_dir($this->cacheDir));
    }

    public function testCacheCreatesDirectoryIfNotExists()
    {
        $this->assertFalse(is_dir($this->cacheDir));

        $cache = new Cache($this->cacheDir);

        $this->assertTrue(is_dir($this->cacheDir));
    }

    public function testSetAndGetDirectory()
    {
        $cache = new Cache($this->cacheDir);

        $this->assertEquals(realpath($this->cacheDir), $cache->getDirectory());
    }

    public function testSetCachesTemplateContent()
    {
        $cache = new Cache($this->cacheDir);

        // Create a temporary template file
        $templateFile = $this->tempDir . '/test.php';
        file_put_contents($templateFile, '<?php echo "original"; ?>');

        $content = '<?php echo "cached content"; ?>';
        $cache->set($templateFile, $content, 'test_template');

        // Verify cache file was created
        $files = glob($this->cacheDir . '/test_template_*.php');
        $this->assertCount(1, $files);

        // Verify content matches
        $cachedContent = file_get_contents($files[0]);
        $this->assertEquals($content, $cachedContent);
    }

    public function testGetReturnsCachedFileIfValid()
    {
        $cache = new Cache($this->cacheDir);

        // Create template file
        $templateFile = $this->tempDir . '/test.php';
        file_put_contents($templateFile, '<?php echo "test"; ?>');

        // Cache the content
        $content = '<?php echo "cached"; ?>';
        $cache->set($templateFile, $content, 'test');

        // Get should return the cache path
        $cachedPath = $cache->get($templateFile, 'test');

        $this->assertNotFalse($cachedPath);
        $this->assertTrue(file_exists($cachedPath));
        $this->assertEquals($content, file_get_contents($cachedPath));
    }

    public function testGetReturnsFalseWhenCacheDoesNotExist()
    {
        $cache = new Cache($this->cacheDir);

        $templateFile = $this->tempDir . '/nonexistent.php';
        file_put_contents($templateFile, '<?php echo "test"; ?>');

        $result = $cache->get($templateFile, 'nonexistent');

        $this->assertFalse($result);
    }

    public function testGetInvalidatesCacheWhenSourceFileIsNewer()
    {
        $cache = new Cache($this->cacheDir);

        // Create and cache template
        $templateFile = $this->tempDir . '/test.php';
        file_put_contents($templateFile, '<?php echo "original"; ?>');

        $cache->set($templateFile, '<?php echo "cached"; ?>', 'test');

        // Verify cache exists
        $cachedPath = $cache->get($templateFile, 'test');
        $this->assertNotFalse($cachedPath);

        // Wait a moment to ensure different mtime
        sleep(1);

        // Modify the source file (making it newer)
        touch($templateFile);

        // Cache should now be invalidated
        $result = $cache->get($templateFile, 'test');
        $this->assertFalse($result);

        // Cached file should be deleted
        $this->assertFalse(file_exists($cachedPath));
    }

    public function testGetReturnsFalseWhenSourceFileDoesNotExist()
    {
        $cache = new Cache($this->cacheDir);

        $templateFile = $this->tempDir . '/missing.php';

        $result = $cache->get($templateFile, 'missing');

        $this->assertFalse($result);
    }

    public function testTemplateSanitization()
    {
        $cache = new Cache($this->cacheDir);

        $templateFile = $this->tempDir . '/test.php';
        file_put_contents($templateFile, '<?php echo "test"; ?>');

        // Use template name with special characters
        $cache->set($templateFile, '<?php echo "cached"; ?>', 'path/to/template.php');

        // Should sanitize to valid filename
        $cachedPath = $cache->get($templateFile, 'path/to/template.php');

        $this->assertNotFalse($cachedPath);
        $this->assertStringContainsString('path_to_template_', basename($cachedPath));
    }

    public function testSetThrowsExceptionOnWriteFailure()
    {
        $cache = new Cache($this->cacheDir);

        // Make cache directory read-only to trigger write failure
        chmod($this->cacheDir, 0400);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot write cached version of template');

        $templateFile = $this->tempDir . '/test.php';
        file_put_contents($templateFile, '<?php echo "test"; ?>');

        try {
            // Suppress expected warning from file_put_contents failure
            @$cache->set($templateFile, '<?php echo "content"; ?>', 'test');
        } finally {
            // Restore permissions for cleanup
            chmod($this->cacheDir, 0750);
        }
    }

    public function testCacheFilePermissions()
    {
        $cache = new Cache($this->cacheDir);

        $templateFile = $this->tempDir . '/test.php';
        file_put_contents($templateFile, '<?php echo "test"; ?>');

        $cache->set($templateFile, '<?php echo "cached"; ?>', 'test');

        $files = glob($this->cacheDir . '/test_*.php');
        $this->assertCount(1, $files);

        // Check file permissions (should be 0640 or more restrictive)
        $perms = fileperms($files[0]) & 0777;
        $this->assertEquals(0640, $perms, 'Cache file should have 0640 permissions');
    }

    public function testCacheDifferentTemplatesWithSameContent()
    {
        $cache = new Cache($this->cacheDir);

        $template1 = $this->tempDir . '/template1.php';
        $template2 = $this->tempDir . '/template2.php';

        file_put_contents($template1, '<?php echo "content"; ?>');
        file_put_contents($template2, '<?php echo "content"; ?>');

        $content = '<?php echo "cached"; ?>';
        $cache->set($template1, $content, 'template1');
        $cache->set($template2, $content, 'template2');

        // Should create separate cache files
        $files = glob($this->cacheDir . '/*.php');
        $this->assertCount(2, $files);

        // Both should be retrievable
        $this->assertNotFalse($cache->get($template1, 'template1'));
        $this->assertNotFalse($cache->get($template2, 'template2'));
    }

    public function testSetReturnsCache()
    {
        $cache = new Cache($this->cacheDir);

        $templateFile = $this->tempDir . '/test.php';
        file_put_contents($templateFile, '<?php echo "test"; ?>');

        $result = $cache->set($templateFile, '<?php echo "cached"; ?>', 'test');

        // Should return Cache instance for fluent interface
        $this->assertInstanceOf(Cache::class, $result);
        $this->assertSame($cache, $result);
    }

    public function testSetDirectoryReturnsCache()
    {
        $cache = new Cache($this->cacheDir);
        $newDir = $this->tempDir . '/newcache';

        $result = $cache->setDirectory($newDir);

        // Should return Cache instance for fluent interface
        $this->assertInstanceOf(Cache::class, $result);
        $this->assertSame($cache, $result);
        $this->assertEquals(realpath($newDir), $cache->getDirectory());
    }

    public function testCacheWithPhpExtensionRemoved()
    {
        $cache = new Cache($this->cacheDir);

        $templateFile = $this->tempDir . '/test.php';
        file_put_contents($templateFile, '<?php echo "test"; ?>');

        // Template name with .php should have it stripped
        $cache->set($templateFile, '<?php echo "cached"; ?>', 'mytemplate.php');

        $files = glob($this->cacheDir . '/mytemplate_*.php');
        $this->assertCount(1, $files);

        // Should not have double .php extension
        $this->assertStringNotContainsString('.php.php', basename($files[0]));
    }
}
