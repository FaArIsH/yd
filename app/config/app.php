<?php
/**
 * Application Configuration
 *
 * @since 0.1
 */

return [

    /**
     * Full URL of your site. MUST end with a trailing slash ( / )
     * Ex. https://yoursite.com/
     * Usually detects automagically! You can change it manually, if you don't like wizards :(
     *
     * @var string
     */
    'url' => oishy_detect_url(),

    /**
     * Set PHP's internal character set
     *
     * @var string
     */
    'charset' => 'UTF-8',

    /**
     * Set the server timezone. Set it to FALSE to use default server timezone.
     *
     * @var string|boolean
     */
    'timezone' => 'Asia/Dhaka',

    /**
     * Set the session save path. Set it to FALSE to use default server's configuration.
     *
     * @var string|boolean
     */
    'session_save_path' => syspath('sessions')
    ];
