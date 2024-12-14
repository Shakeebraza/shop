<?php
session_start();

// Set appropriate headers
header('Content-type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Expires: Sat, 1 Jan 2000 00:00:00 GMT');

// Define dimensions (30% smaller than original)
$width = 110 * 0.7;
$height = 76 * 0.7;

// Create image and allocate colors
$image = imagecreatetruecolor($width, $height);
$bg_color = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 0, 0, 0);
$noise_color = imagecolorallocate($image, 100, 100, 100);

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Generate CAPTCHA code
$captcha_code = '';
for ($i = 0; $i < 5; $i++) {
    $captcha_code .= random_int(0, 9); // Secure random digits (0-9)
}
$_SESSION['captcha_code'] = $captcha_code;

// Add noise (dots and lines)
for ($i = 0; $i < 50; $i++) {
    imagesetpixel($image, random_int(0, $width), random_int(0, $height), $noise_color);
}
for ($i = 0; $i < 5; $i++) {
    imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $noise_color);
}

// Set font and randomize text position
$font_path = __DIR__ . '/fonts/arial.ttf';  // Path to a TTF font file
$font_size = 14;
$x = random_int(10, 20);
$y = random_int(25, 35);

// Render CAPTCHA text with custom font
imagettftext($image, $font_size, random_int(-10, 10), $x, $y, $text_color, $font_path, $captcha_code);

// Output image and clean up
imagepng($image);
imagedestroy($image);
?>
