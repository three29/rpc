<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\Image;

class ImageTest extends TestCase
{
    private $testImagePath;
    private $testNonImagePath;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary test image (1x1 pixel PNG)
        $this->testImagePath = sys_get_temp_dir() . '/test_image_' . uniqid() . '.png';
        $img = imagecreatetruecolor(1, 1);
        $color = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $color);
        imagepng($img, $this->testImagePath);
        // imagedestroy() is deprecated in PHP 8.5
        // imagedestroy($img);

        // Create a non-image file
        $this->testNonImagePath = sys_get_temp_dir() . '/test_text_' . uniqid() . '.txt';
        file_put_contents($this->testNonImagePath, 'This is not an image');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testImagePath)) {
            unlink($this->testImagePath);
        }
        if (file_exists($this->testNonImagePath)) {
            unlink($this->testNonImagePath);
        }
        parent::tearDown();
    }

    public function testValidatesImageFile()
    {
        $validator = new Image();
        $result = $validator->validate($this->testImagePath);
        $this->assertNotFalse($result, 'Expected valid image to pass validation');
    }

    public function testRejectsNonImageFile()
    {
        $validator = new Image();
        $result = $validator->validate($this->testNonImagePath);
        $this->assertFalse($result, 'Expected non-image file to fail validation');
    }

    public function testRejectsNonExistentFile()
    {
        $validator = new Image();

        // Suppress the warning that getimagesize() generates for non-existent files
        $result = @$validator->validate('/path/to/nonexistent/file.jpg');
        $this->assertFalse($result, 'Expected non-existent file to fail validation');
    }

    public function testRejectsEmptyString()
    {
        $validator = new Image();

        // In PHP 8.x, passing empty string to getimagesize() throws ValueError
        // We need to catch it instead of suppressing all errors
        try {
            $result = $validator->validate('');
            $this->assertFalse($result, 'Expected empty string to fail validation');
        } catch (\ValueError $e) {
            // This is expected in PHP 8.x
            $this->assertTrue(true, 'ValueError thrown as expected for empty path');
        }
    }
}
