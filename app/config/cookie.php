<?php
/**
 * Cookie Configuration, affects session as well
 *
 * @since 0.1
 */

return [

    /**
     * The path the cookie is valid on, usually "/" to make it valid on the whole domain.
     *
     * @var string
     */
    'path' => '/',

    /**
     * The domain where the cookie is valid for. Usually this does not work with
     * "localhost", ".localhost", "127.0.0.1" or ".127.0.0.1". If so, leave it
     * as empty string, false or null. When using real domains make sure you have
     * a dot(.) in front of the domain, like ".mydomain.com"
     *
     * @var string
     */
    'domain' => '',

    /**
     * If the cookie will be transferred through secured connection(SSL). It's
     * highly recommended to set it to TRUE if you have SSL enabled.
     *
     * @var boolean
     */
    'secure' => false,

    /**
     * If set to true, Cookies can't be accessed by JavaScript
     * TRUE is Highly recommended!
     *
     * @var boolean
     */
    'httponly' => true,

    /**
     * Session Lifetime in seconds, 0 = Till closing the browser
     *
     * @var integer
     */
    'session_lifetime' => 0,

    /**
     * Session cookie name
     *
     * @var string|boolean
     */
    'session_cookie_name' => '___oishy_sessid'
    ];
