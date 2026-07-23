<?php

namespace RPC;

use RPC\Exception\InvalidArgumentException;

/**
 * Very simple image class which allows for resizing and converting
 * between formats
 *
 * @package Core
 */
class Image
{

	// *** Class variables
		private $image;
	    private int $width;
	    private int $height;
		private $imageResized;

		function __construct(string $fileName)
		{
			// *** Open up the file
			$this->image = $this->openImage($fileName);

		    // *** Get width and height
		    $this->width  = imagesx($this->image);
		    $this->height = imagesy($this->image);
		}

		## --------------------------------------------------------

		private function openImage(string $file)
		{
			// *** Get extension
			$extension = strtolower(strrchr($file, '.'));

			switch($extension)
			{
				case '.jpg':
				case '.jpeg':
					$img = @imagecreatefromjpeg($file);
					break;
				case '.gif':
					$img = @imagecreatefromgif($file);
					break;
				case '.png':
					$img = @imagecreatefrompng($file);
					break;
				default:
					throw new InvalidArgumentException( 'This is not an image' );
			}
			return $img;
		}

		## --------------------------------------------------------

		public function resize(int $newWidth, int $newHeight, string $option="auto"): void
		{
			// *** Get optimal width and height - based on $option
			$optionArray = $this->getDimensions($newWidth, $newHeight, $option);

			$optimalWidth  = (int)$optionArray['optimalWidth'];
			$optimalHeight = (int)$optionArray['optimalHeight'];


			// *** Resample - create image canvas of x, y size
			$this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
			imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);


			// *** if option is 'crop', then crop too
			if ($option == 'crop') {
				$this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
			}
		}

		## --------------------------------------------------------

		private function getDimensions(int $newWidth, int $newHeight, string $option): array
		{

		$optimalWidth = 0;
		$optimalHeight = 0;
		   switch ($option)
			{
				case 'exact':
					$optimalWidth = $newWidth;
					$optimalHeight= $newHeight;
					break;
				case 'portrait':
					$optimalWidth = $this->getSizeByFixedHeight($newHeight);
					$optimalHeight= $newHeight;
					break;
				case 'landscape':
					$optimalWidth = $newWidth;
					$optimalHeight= $this->getSizeByFixedWidth($newWidth);
					break;
				case 'auto':
					$optionArray = $this->getSizeByAuto($newWidth, $newHeight);
					$optimalWidth = $optionArray['optimalWidth'];
					$optimalHeight = $optionArray['optimalHeight'];
					break;
				case 'crop':
					$optionArray = $this->getOptimalCrop($newWidth, $newHeight);
					$optimalWidth = $optionArray['optimalWidth'];
					$optimalHeight = $optionArray['optimalHeight'];
					break;
			}
			return array('optimalWidth' => (int)$optimalWidth, 'optimalHeight' => (int)$optimalHeight);
		}

		## --------------------------------------------------------

		private function getSizeByFixedHeight(int $newHeight): float
		{
			$ratio = $this->width / $this->height;
			$newWidth = $newHeight * $ratio;
			return $newWidth;
		}

		private function getSizeByFixedWidth(int $newWidth): float
		{
			$ratio = $this->height / $this->width;
			$newHeight = $newWidth * $ratio;
			return $newHeight;
		}

		private function getSizeByAuto(int $newWidth, int $newHeight): array
		{
			if ($this->height < $this->width)
			// *** Image to be resized is wider (landscape)
			{
				$optimalWidth = $newWidth;
				$optimalHeight= $this->getSizeByFixedWidth($newWidth);
			}
			elseif ($this->height > $this->width)
			// *** Image to be resized is taller (portrait)
			{
				$optimalWidth = $this->getSizeByFixedHeight($newHeight);
				$optimalHeight= $newHeight;
			}
			else
			// *** Image to be resizerd is a square
			{
				if ($newHeight < $newWidth) {
					$optimalWidth = $newWidth;
					$optimalHeight= $this->getSizeByFixedWidth($newWidth);
				} else if ($newHeight > $newWidth) {
					$optimalWidth = $this->getSizeByFixedHeight($newHeight);
					$optimalHeight= $newHeight;
				} else {
					// *** Sqaure being resized to a square
					$optimalWidth = $newWidth;
					$optimalHeight= $newHeight;
				}
			}

			return array('optimalWidth' => (int)$optimalWidth, 'optimalHeight' => (int)$optimalHeight);
		}

		## --------------------------------------------------------

		private function getOptimalCrop(int $newWidth, int $newHeight): array
		{

			$heightRatio = $this->height / $newHeight;
			$widthRatio  = $this->width /  $newWidth;

			if ($heightRatio < $widthRatio) {
				$optimalRatio = $heightRatio;
			} else {
				$optimalRatio = $widthRatio;
			}

			$optimalHeight = $this->height / $optimalRatio;
			$optimalWidth  = $this->width  / $optimalRatio;

			return array('optimalWidth' => (int)$optimalWidth, 'optimalHeight' => (int)$optimalHeight);
		}

		## --------------------------------------------------------

		private function crop(float $optimalWidth, float $optimalHeight, int $newWidth, int $newHeight): void
		{
			// *** Find center - this will be used for the crop
			$cropStartX = (int)(( $optimalWidth / 2) - ( $newWidth /2 ));
			$cropStartY = (int)(( $optimalHeight/ 2) - ( $newHeight/2 ));

			$crop = $this->imageResized;
			//imagedestroy($this->imageResized);

			// *** Now crop from center to exact requested size
			$this->imageResized = imagecreatetruecolor($newWidth , $newHeight);
			imagecopyresampled($this->imageResized, $crop , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
		}

		## --------------------------------------------------------

		public function save(string $savePath, string|int $imageQuality="100"): void
		{
			// *** Get extension
    		$extension = strrchr($savePath, '.');
   			$extension = strtolower($extension);

			switch($extension)
			{
				case '.jpg':
				case '.jpeg':
					if (imagetypes() & IMG_JPG) {
						imagejpeg($this->imageResized, $savePath, $imageQuality);
					}
					break;

				case '.gif':
					if (imagetypes() & IMG_GIF) {
						imagegif($this->imageResized, $savePath);
					}
					break;

				case '.png':
					// *** Scale quality from 0-100 to 0-9
					$scaleQuality = (int) round(($imageQuality/100) * 9);

					// *** Invert quality setting as 0 is best, not 9
					$invertScaleQuality = 9 - $scaleQuality;

					if (imagetypes() & IMG_PNG) {
						 imagepng($this->imageResized, $savePath, $invertScaleQuality);
					}
					break;

				// ... etc

				default:
					// *** No extension - No save.
					throw new InvalidArgumentException( 'File has no extension.' );

		}
			// imagedestroy() is no longer needed in PHP 8.0+ - GdImage objects are automatically destroyed
		}


		public function rotateImage( string $savePath, int $angle = 90 ): void
		{
			$imageQuality = 100;

			// *** Get extension
    		$extension = strrchr($savePath, '.');
   			$extension = strtolower($extension);

			// Create a transparent background color for rotation (PHP 8.5 compatible)
			$transparent = imagecolorallocatealpha($this->image, 0, 0, 0, 127);
			$this->image = imagerotate( $this->image, $angle, $transparent );

			switch($extension)
			{
				case '.jpg':
				case '.jpeg':
					if (imagetypes() & IMG_JPG) {
						imagejpeg( $this->image, $savePath, $imageQuality );
					}
					break;

				case '.gif':
					if (imagetypes() & IMG_GIF) {
						imagegif($this->image, $savePath);
					}
					break;

				case '.png':
					// *** Scale quality from 0-100 to 0-9
					$scaleQuality = (int) round(($imageQuality/100) * 9);

					// *** Invert quality setting as 0 is best, not 9
					$invertScaleQuality = 9 - $scaleQuality;

					if (imagetypes() & IMG_PNG) {
						 imagepng($this->image, $savePath, $invertScaleQuality);
					}
					break;

				// ... etc

				default:
					// *** No extension - No save.
					break;
			}

			// imagedestroy() is no longer needed in PHP 8.0+ - GdImage objects are automatically destroyed
		}

		## --------------------------------------------------------


}

?>
