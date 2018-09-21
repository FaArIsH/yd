<?php
namespace Oishy\Core;

/**
 * The Request Wrapper Class.
 *
 * @version 1.0
 * @since 0.1
 */

class Request
{

    /**
     * Gets/returns the value of a specific key of the GET super-global.
     *
     * @param  mixed  $key     The Key
     * @param  mixed $fallback Fall-back value
     * @return mixed
     */
    public static function get($key, $fallback = false)
    {
        return Arr::get($_GET, $key, $fallback);
    }

    /**
     * Gets/returns the value of a specific key of the POST super-global.
     *
     * @param  mixed  $key     The Key
     * @param  mixed $fallback Fall-back value
     * @return mixed
     */
    public static function post($key, $fallback = false)
    {
        return Arr::get($_POST, $key, $fallback);
    }

    /**
     * Gets/returns the value of a specific key of the SERVER super-global.
     *
     * @param  mixed  $key     The Key
     * @param  mixed $fallback Fall-back value
     * @return mixed
     */
    public static function server($key, $fallback = false)
    {
        return Arr::get($_SERVER, $key, $fallback);
    }

    /**
     * Gets/returns the value of a specific key of the FILES super-global.
     *
     * @param  mixed  $key     The Key
     * @param  mixed $fallback Fall-back value
     * @return mixed
     */
    public static function files($key, $fallback = false)
    {
        return Arr::get($_FILES, $key, $fallback);
    }

    /**
     * Checks if the current request was sent
     * with a XMLHttpRequest header as sent by javascript
     *
     * @return boolean
     */
    public static function isAjax()
    {
        return strcasecmp(self::server('HTTP_X_REQUESTED_WITH'), 'xmlhttprequest') === 0;
    }
}
