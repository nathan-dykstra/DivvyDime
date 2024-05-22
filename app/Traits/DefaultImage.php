<?php

namespace App\Traits;

use GdImage;

trait DefaultImage
{
    /**
     * Generates a default image using the first letter of $image_text
     * @param string $filepath The path to save the image
     * @param string $filename The name of the image file
     * @param string $image_text The text to display on the image
     * @param int $font_size The size of the text on the image
     * @param int $image_width The width of the image
     * @param int $image_height The height of the image
     * @return string The URL of the generated image
     */
    public function createDefaultImage(string $filepath, string $filename, string $image_text, int $font_size = 100, int $image_width = 200, int $image_height = 200): string
    {
        $image_path = public_path($filepath . $filename);
        $initial = strtoupper($image_text[0]);

        // Background colours for the gradient
        $bg_colour_start = '#'.substr(md5($image_text . 'start'), 0, 6); // Unique colours based on username
        $bg_colour_end = '#'.substr(md5($image_text . 'end'), 0, 6);
        $bg_colour_start = $this->adjustColorIfTooLight($bg_colour_start); // Adjust colours if luminance is too high
        $bg_colour_end = $this->adjustColorIfTooLight($bg_colour_end);

        $image = imagecreatetruecolor($image_width, $image_height);

        // Create the gradient background
        $this->createDiagonalGradient($image, $bg_colour_start, $bg_colour_end);

        // Set up text and font
        $text_color = '#ffffff';
        $text = imagecolorallocate($image, hexdec(substr($text_color, 1, 2)), hexdec(substr($text_color, 3, 2)), hexdec(substr($text_color, 5, 2)));
        $font = public_path('fonts/ARIAL.TTF');

        // Get the bounding box of the text
        $bbox = imagettfbbox($font_size, 0, $font, $initial);
        $text_width = $bbox[2] - $bbox[0]; // Bottom right (x) minus bottom left (x)
        $text_height = $bbox[1] - $bbox[7]; // Top left (y) minus bottom left (y)

        // Calculate x and y coordinates to center the text
        $x = ($image_width - $text_width) / 2;
        $y = ($image_height + $text_height) / 2;

        // Add the text to the image
        imagettftext($image, $font_size, 0, $x, $y, $text, $font, $initial);

        // Save the image file
        imagepng($image, $image_path);
        imagedestroy($image);

        return asset($filepath . $filename);
    }

    /**
     * Creates a diagonal gradient from $start_colour (top left) to $end_colour
     * (bottom right) on $image
     * @param GdImage $image The image to apply the gradient to
     * @param string $start_colour The colour to start the gradient
     * @param string $end_colour The colour to end the gradient
     */
    protected function createDiagonalGradient(GdImage $image, string $start_colour, string $end_colour)
    {
        list($r1, $g1, $b1) = sscanf($start_colour, "#%02x%02x%02x");
        list($r2, $g2, $b2) = sscanf($end_colour, "#%02x%02x%02x");

        $width = imagesx($image);
        $height = imagesy($image);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                // Calculate the interpolation factor based on the current pixel's distance from the top left corner
                $distance = sqrt($x * $x + $y * $y);
                $max_distance = sqrt($width * $width + $height * $height);
                $factor = $distance / $max_distance;

                // Interpolate the RGB values
                $r = (int)($r1 + ($r2 - $r1) * $factor);
                $g = (int)($g1 + ($g2 - $g1) * $factor);
                $b = (int)($b1 + ($b2 - $b1) * $factor);

                $color = imagecolorallocate($image, $r, $g, $b);
                imagesetpixel($image, $x, $y, $color);
            }
        }
    }

    /**
     * Adjusts $colour if its luminance is greater than $threshold
     * @param string $colour The colour to adjust
     * @param float $threshold The maximum luminance allowed
     * @return string The adjusted colour
     */
    protected function adjustColorIfTooLight(string $colour, float $threshold = 0.8): string
    {
        list($r, $g, $b) = sscanf($colour, "#%02x%02x%02x");
        $luminance = $this->calculateLuminance($r, $g, $b);

        while ($luminance > $threshold) {
            $r = (int)($r * 0.7);
            $g = (int)($g * 0.7);
            $b = (int)($b * 0.7);
            $luminance = $this->calculateLuminance($r, $g, $b);
            $colour = sprintf("#%02x%02x%02x", $r, $g, $b);
        }

        return $colour;
    }

    /**
     * Calculates the luminance of an RGB colour using the standard formula
     * @param int $r The red component of the colour
     * @param int $g The green component of the colour
     * @param int $b The blue component of the colour
     * @return float The luminance of the colour
     */
    protected function calculateLuminance(int $r, int $g, int $b): float
    {
        return (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;
    }
}
