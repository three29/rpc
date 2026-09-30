<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RPC\Image;
use RPC\Exception\InvalidArgumentException;

class ImageTest extends TestCase
{
    private string $testImagePath;
    private string $testOutputPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary test image (1x1 red pixel PNG)
        $this->testImagePath = sys_get_temp_dir() . '/test_image_' . uniqid() . '.png';
        $this->testOutputPath = sys_get_temp_dir() . '/test_output_' . uniqid() . '.png';

        $img = imagecreatetruecolor(100, 100);
        $red = imagecolorallocate($img, 255, 0, 0);
        imagefill($img, 0, 0, $red);
        imagepng($img, $this->testImagePath);
        // imagedestroy() is deprecated in PHP 8.5 - GdImage objects are auto-destroyed
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testImagePath)) {
            unlink($this->testImagePath);
        }
        if (file_exists($this->testOutputPath)) {
            unlink($this->testOutputPath);
        }

        parent::tearDown();
    }

    public function testConstructorThrowsExceptionForInvalidFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('This is not an image');

        new Image(__DIR__ . '/nonexistent.txt');
    }

    public function testConstructorLoadsImage(): void
    {
        $image = new Image($this->testImagePath);

        $this->assertInstanceOf(Image::class, $image);
    }

    public function testResizeExact(): void
    {
        $image = new Image($this->testImagePath);
        $image->resize(50, 50, 'exact');
        $image->save($this->testOutputPath, 100);

        $this->assertFileExists($this->testOutputPath);

        list($width, $height) = getimagesize($this->testOutputPath);
        $this->assertSame(50, $width);
        $this->assertSame(50, $height);
    }

    public function testResizeAuto(): void
    {
        $image = new Image($this->testImagePath);
        $image->resize(50, 50, 'auto');
        $image->save($this->testOutputPath, 100);

        $this->assertFileExists($this->testOutputPath);
        list($width, $height) = getimagesize($this->testOutputPath);
        $this->assertSame(50, $width);
    }

    public function testSaveAsPng(): void
    {
        $image = new Image($this->testImagePath);
        $outputPath = sys_get_temp_dir() . '/test_output_' . uniqid() . '.png';

        $image->resize(50, 50, 'exact');
        $image->save($outputPath, 100);

        $this->assertFileExists($outputPath);

        $imageInfo = getimagesize($outputPath);
        $this->assertSame(IMAGETYPE_PNG, $imageInfo[2]);

        unlink($outputPath);
    }

    public function testSaveAsJpg(): void
    {
        $image = new Image($this->testImagePath);
        $outputPath = sys_get_temp_dir() . '/test_output_' . uniqid() . '.jpg';

        $image->resize(50, 50, 'exact');
        $image->save($outputPath, 90);

        $this->assertFileExists($outputPath);

        $imageInfo = getimagesize($outputPath);
        $this->assertSame(IMAGETYPE_JPEG, $imageInfo[2]);

        unlink($outputPath);
    }

    public function testSaveAsGif(): void
    {
        $image = new Image($this->testImagePath);
        $outputPath = sys_get_temp_dir() . '/test_output_' . uniqid() . '.gif';

        $image->resize(50, 50, 'exact');
        $image->save($outputPath, 100);

        $this->assertFileExists($outputPath);

        $imageInfo = getimagesize($outputPath);
        $this->assertSame(IMAGETYPE_GIF, $imageInfo[2]);

        unlink($outputPath);
    }

    public function testSaveThrowsExceptionForInvalidExtension(): void
    {
        $image = new Image($this->testImagePath);
        $image->resize(50, 50, 'exact');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File has no extension');

        $image->save('/tmp/noextension', 100);
    }

    public function testRotateImage(): void
    {
        $image = new Image($this->testImagePath);
        $outputPath = sys_get_temp_dir() . '/test_rotated_' . uniqid() . '.jpg';

        $image->rotateImage($outputPath, 90);

        $this->assertFileExists($outputPath);

        unlink($outputPath);
    }

    public function testResizePortrait(): void
    {
        // Create a portrait image (taller than wide)
        $portraitPath = sys_get_temp_dir() . '/test_portrait_' . uniqid() . '.png';
        $img = imagecreatetruecolor(50, 100);
        $blue = imagecolorallocate($img, 0, 0, 255);
        imagefill($img, 0, 0, $blue);
        imagepng($img, $portraitPath);
        // imagedestroy() is deprecated in PHP 8.5 - GdImage objects are auto-destroyed

        $image = new Image($portraitPath);
        $image->resize(25, 50, 'portrait');
        $image->save($this->testOutputPath, 100);

        $this->assertFileExists($this->testOutputPath);
        list($width, $height) = getimagesize($this->testOutputPath);
        $this->assertSame(50, $height);

        unlink($portraitPath);
    }

    public function testResizeLandscape(): void
    {
        // Create a landscape image (wider than tall)
        $landscapePath = sys_get_temp_dir() . '/test_landscape_' . uniqid() . '.png';
        $img = imagecreatetruecolor(100, 50);
        $green = imagecolorallocate($img, 0, 255, 0);
        imagefill($img, 0, 0, $green);
        imagepng($img, $landscapePath);
        // imagedestroy() is deprecated in PHP 8.5 - GdImage objects are auto-destroyed

        $image = new Image($landscapePath);
        $image->resize(50, 25, 'landscape');
        $image->save($this->testOutputPath, 100);

        $this->assertFileExists($this->testOutputPath);
        list($width, $height) = getimagesize($this->testOutputPath);
        $this->assertSame(50, $width);

        unlink($landscapePath);
    }

    public function testResizeCrop(): void
    {
        $image = new Image($this->testImagePath);
        $image->resize(50, 50, 'crop');
        $image->save($this->testOutputPath, 100);

        $this->assertFileExists($this->testOutputPath);
        list($width, $height) = getimagesize($this->testOutputPath);
        $this->assertSame(50, $width);
        $this->assertSame(50, $height);
    }
}
