<?php

namespace App\Traits;

trait DefaultImage
{
    /**
     * 
     */
    public function createDefaultImage($ilepath, $filename, $image_text, $font_size = 100)
    {
        $image_path = public_path($ilepath . $filename);
        $initial = strtoupper($image_text[0]);

        // Background colours for the gradient
        $bg_colour_start = '#'.substr(md5($image_text . 'start'), 0, 6); // Unique color based on username
        $bg_colour_end = '#'.substr(md5($image_text . 'end'), 0, 6); // Another unique color based on username

        $image = imagecreatetruecolor(200, 200);

        // Create the gradient background
        $this->createDiagonalGradient($image, $bg_colour_start, $bg_colour_end);

        // Set up text and font
        $text_color = '#ffffff';
        $text = imagecolorallocate($image, hexdec(substr($text_color, 1, 2)), hexdec(substr($text_color, 3, 2)), hexdec(substr($text_color, 5, 2)));
        $font_path = public_path('fonts/ARIAL.TTF');

        // Get the bounding box of the text
        $bbox = imagettfbbox($font_size, 0, $font_path, $initial);
        $text_width = $bbox[2] - $bbox[0];
        $text_height = $bbox[1] - $bbox[7];

        // Calculate x and y coordinates to center the text
        $x = (200 - $text_width) / 2;
        $y = (200 + $text_height) / 2;

        // Add the text to the image
        imagettftext($image, $font_size, 0, $x, $y, $text, $font_path, $initial);

        // Save the image file
        imagepng($image, $image_path);
        imagedestroy($image);

        return asset($ilepath . $filename);
    }

    /**
     * 
     */
    protected function createDiagonalGradient($image, $start_colour, $end_colour)
    {
        list($r1, $g1, $b1) = sscanf($start_colour, "#%02x%02x%02x");
        list($r2, $g2, $b2) = sscanf($end_colour, "#%02x%02x%02x");

        $width = imagesx($image);
        $height = imagesy($image);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                // Calculate the interpolation factor based on the position
                $distance = sqrt($x * $x + $y * $y);
                $max_distance = sqrt($width * $width + $height * $height);
                $factor = $distance / $max_distance;

                $r = (int)($r1 + ($r2 - $r1) * $factor);
                $g = (int)($g1 + ($g2 - $g1) * $factor);
                $b = (int)($b1 + ($b2 - $b1) * $factor);

                $color = imagecolorallocate($image, $r, $g, $b);
                imagesetpixel($image, $x, $y, $color);
            }
        }
    }
}