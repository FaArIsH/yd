<?php
namespace Oishy\Core;

/**
* The Cookie Wrapper Class
*
* @version 1.0
* @since 0.1
*/

class Cookie
{

    /**
     * Set a cookie
     *
     * @param mixed $name      The name of the cookie.
     *
     * @param string $value    (optional) The value of the cookie.
     *
     * @param integer $expire  Specifies when the cookie expires, by default
     *                         it's set to untill the browser is closed
     *
     * @param string $path     (optional) Specifies the server path of the cookie,
     *                         by default the Config value will be used.
     * @param string $domain   (optional) Specifies the domain name of the cookie,
     *                         by default the Config value will be used.
     *
     * @param boolean $secure  (optional) Specifies whether or not the cookie
     *                         should only be transmitted over a secure HTTPS
     *                         connection.
     *                         By default the Config value will be used.
     *
     * @param boolean $httponly (optional) If set to TRUE the cookie will be accessible
     *                           only through the HTTP protocol.
     *                           By default the Config value will be used.
     * @throws \Exception If headers sent already.
     * @return boolean
     */
    public static function set(
        $name,
        $value = '',
        $expire = 0,
        $path = '',
        $domain = '',
        $secure = '',
        $httponly = ''
    ) {
        // Set default cookie path if not defined
        if ($path === '') {
            $path = Config::get('cookie.path');
        }

        // Set default cookie domain if not defined
        if ($domain === '') {
            $domain = Config::get('cookie.domain');
        }

        // Set default ssl only cookie status if not defined
        if ($secure === '') {
            $secure = Config::get('cookie.secure');
        }

        // Set default http only cookie status if not defined
        if ($httponly === '') {
            $httponly = Config::get('cookie.httponly');
        }

        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    /**
     * Get a cookie value by its key/name
     *
     * @param  mixed  $name    The cookie key/name
     * @param  mixed $fallback Fall-back value
     * @return mixed
     */
    public static function get($name, $fallback = false)
    {
        return array_key_exists($name, $_COOKIE) ? $_COOKIE[$name] : $fallback;
    }

    /**
     * Remove a Cookie
     *
     * @param  mixed $name The cookie name
     * @throws \Exception If headers sent already.
     * @return
     */
    public static function remove($name)
    {
        setcookie($name, "", 1);
        setcookie($name, false);
        unset($_COOKIE[$name]);
    }

    /**
     * Flush all cookies ( including sessions )
     *
     * @return boolean
     */
    public static function flush()
    {
        $cookies = explode(';', Request::server('HTTP_COOKIE', ''));

        foreach ($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            self::remove($name);
        }

        return true;
    }
}
