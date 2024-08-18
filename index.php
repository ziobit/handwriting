<?php

echo "Write something on paper, take a picture, and this will make a transparent PNG of your handwriting.<br>";
echo "Converts qqq.jpg into handwriting_transparent.png<br>";
define (TH, 120);

// Load your image

$image = imagecreatefromjpeg('qqq.jpg');

// Get image dimensions
$width = imagesx($image);
$height = imagesy($image);

// Create a new true color image with transparency
$transparentImage = imagecreatetruecolor($width, $height);
imagesavealpha($transparentImage, true);
$transparency = imagecolorallocatealpha($transparentImage, 0, 0, 0, 127);
imagefill($transparentImage, 0, 0, $transparency);

// Set the threshold for the white background
$white = imagecolorallocate($image, TH, TH, TH);

// Iterate over each pixel to set transparency
for ($x = 0; $x < $width; $x++) {
  for ($y = 0; $y < $height; $y++) {
    // Get the current pixel's color
    $rgb = imagecolorat($image, $x, $y);
    $colors = imagecolorsforindex($image, $rgb);

    // Check if the current pixel color is greater than the threshold
    if ($colors['red'] > TH && $colors['green'] > TH && $colors['blue'] > TH) {
      // Set it to transparent
      imagesetpixel($transparentImage, $x, $y, $transparency);
    } else {
      // Otherwise, copy the pixel
      $color = imagecolorallocatealpha($transparentImage, $colors['red'], $colors['green'], $colors['blue'], 0);
      imagesetpixel($transparentImage, $x, $y, $color);
    }
  }
}

// Save the resulting image
imagepng($transparentImage, 'handwriting_transparent.png');

// Clean up
imagedestroy($image);
imagedestroy($transparentImage);