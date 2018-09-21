<?php
/**
 * Captcha Library Configuration
 *
 * @since 0.1
 */

return [

    /**
     * Absolute path to the captcha font
     *
     * @var string
     */
    'font_path' => syspath('static/fonts/monofont.ttf'),

    /**
     * Width of the captcha
     *
     * @var integer
     */
    'width' => 140,

    /**
     * Height of the captcha
     *
     * @var integer
     */
    'height' => 50,

    /**
     * Captcha font size
     * Set this to 'auto' to adjust the font size automatically based on width/height
     *
     * @var integer|string
     */
    'font_size' => 34,

    /**
     * Captcha text length
     *
     * @var integer
     */
    'length' => 6,

    /**
     * Text color as hex
     *
     * @var string
     */
    'text_color' => '#111',

    /**
     * Background color as hex
     *
     * @var string
     */
    'bg_color' => '#ffffff',

    /**
     * Number of lines
     */
    'lines' => 10,

    /**
     * Lines color as hex
     *
     * @var string
     */
    'lines_color' => '#096',

    /**
     * Number of dots
     *
     * @var integer
     */
    'dots' => 25,

    /**
     * Dots color as hex
     *
     * @var string
     */
    'dots_color' => '#096',

    /**
     * Whether the captcha is case sensitive or not
     *
     * @var boolean
     */
    'case_sensitive' => true
    ];
