<?php
namespace Oishy\Core;

use RuntimeException;

/**
* Oishy - The Divine Atomic PHP Framework!
*
* A Tiny Captcha generation and validation class for Oishy.
*
* @version 1.0
* @since 0.1
*/

class Captcha
{
    /**
     * Session key to store captcha code
     *
     * @var string
     */
    public static $captcha_session_key = '__oishy_captcha.code';

    /**
     * Render the captcha image. Setting other parameters will override config
     * values.
     *
     * @return
     */
    public static function render()
    {
        // Force typecast to be safe!
        $length = (int)Config::get('captcha.length', 6);
        $width = (int)Config::get('captcha.width');
        $height = (int)Config::get('captcha.height');
        $font_size = (int)Config::get('captcha.font_size');
        $lines = (int)Config::get('captcha.lines');
        $dots = (int)Config::get('captcha.dots');
        // Why I'm using ucwords() here?
        // 'Cause I think the captcha looks cool when the first letter is capital -_-
        // I know, this is lame ;_;
        $text = ucwords(get_random_string($length));
        $font = Config::get('captcha.font_path');
        $text_color_rgb = self::hexToRGB(Config::get('captcha.text_color', '#000000'));
        $bg_color_rgb = self::hexToRGB(Config::get('captcha.bg_color', '#FFFFFF'));
        $lines_color_rgb = self::hexToRGB(Config::get('captcha.lines_color', '#162453'));
        $dots_color_rgb = self::hexToRGB(Config::get('captcha.dots_color', '#162453'));


        // I thought it may come handy :/
        if (!$font_size) {
            $font_size = $height * 0.75;
        }

        if (!is_file($font)) {
            throw new RuntimeException(sprintf('No such font file found at %s!', $font));
        }

        // Start a session if isn't started already
        Session::start();

        $im = imagecreatetruecolor($width, $height);
        $text_color = imagecolorallocate($im, $text_color_rgb['r'], $text_color_rgb['g'],
        $text_color_rgb['b']);
        $bg_color = imagecolorallocate($im, $bg_color_rgb['r'], $bg_color_rgb['g'],
        $bg_color_rgb['b']);
        $lines_color = imagecolorallocate($im, $lines_color_rgb['r'], $lines_color_rgb['g'],
        $lines_color_rgb['b']);
        $dots_color = imagecolorallocate($im, $dots_color_rgb['r'], $dots_color_rgb['g'],
        $dots_color_rgb['b']);

        // Fill the image with background
        imagefill($im, 0, 0, $bg_color);

        // Draw some lines and dots
        self::drawLines($im, $height, $width, $lines, $lines_color);
        self::drawDots($im, $height, $width, $dots, $dots_color);

        list($x, $y) = self::imageTTFCenter($im, $text, $font, $font_size);
        imagettftext($im, $font_size, 0, $x, $y, $text_color, $font, $text);

        // Let the world know it's a freakin' captcha!
        Response::create(200, [
            'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT',
            'Cache-Control' => 'post-check=0, pre-check=0',
            'Pragma' => 'no-cache',
            'Content-Type' => 'image/jpeg'
            ])->send();

        // Output the image
        imagejpeg($im);
        imagedestroy($im);

        // Store the text in session
        Session::set(self::$captcha_session_key, $text);
    }

    protected static function imageTTFCenter($image, $text, $font, $size, $angle = 8)
    {
        $xi = imagesx($image);
        $yi = imagesy($image);
        $box = imagettfbbox($size, $angle, $font, $text);
        $xr = abs(max($box[2], $box[4]));
        $yr = abs(max($box[5], $box[7]));
        $x = intval(($xi - $xr) / 2);
        $y = intval(($yi + $yr) / 2);
        return [$x, $y];
    }

    protected static function drawLines($im, $height, $width, $lines, $lines_color)
    {
        if ($lines > 0) {
            for ($i = 0; $i < $lines; $i++) {
                imageline($im, mt_rand(0, $width), mt_rand(0, $height),
                    mt_rand(0, $width), mt_rand(0, $height), $lines_color);
            }
        }
    }

    protected static function drawDots($im, $height, $width, $dots, $dots_color)
    {
        if ($dots > 0) {
            for ($i = 0; $i < $dots; $i++) {
                imagefilledellipse($im, mt_rand(0, $width),
                mt_rand(0, $height), 3, 3, $dots_color);
            }
        }
    }

    protected static function hexToRGB($colour)
    {
        if ($colour[0] == '#') {
            $colour = substr($colour, 1);
        }
        if (strlen($colour) == 6) {
            list($r, $g, $b) = [ $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] ];
        } elseif (strlen($colour) == 3) {
            list($r, $g, $b) = [ $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] ];
        } else {
            return false;
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return [ 'r' => $r, 'g' => $g, 'b' => $b ];
    }

    /**
     * Validate captcha code against user input
     *
     * @param string $input          User input
     * @param boolean $case_sensitive Toggle case-sensitivity, by default config
     *                                value is used.
     * @return boolean
     */
    public static function validate($input = '', $case_sensitive = '')
    {
        if (!Session::get(self::$captcha_session_key)) {
            return false;
        }

        if ($case_sensitive === '') {
            $case_sensitive = (bool)Config::get('captcha.case_sensitive', true);
        }

        $captcha = (string)Session::get(self::$captcha_session_key);

        // delete the string from session
        // to prevent re-use
        Session::remove(self::$captcha_session_key);

        if (!$case_sensitive) {
            $input = mb_strtolower($input);
            $captcha = mb_strtolower($captcha);
        }
        return $input === $captcha && !empty($input);
    }
}
