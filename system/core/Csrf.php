<?php
namespace Oishy\Core;

/**
* Oishy - The Divine Atomic PHP Framework!
*
* CSRF protection class for Oishy.
*
* @version 1.0
* @since 0.1
*/
class Csrf
{
    /**
     * The session key for storing csrf token
     *
     * @var string
     */
    public static $csrf_token_key = '__oishy_csrf_token';

    /**
     * The session key for storing token expiration time
     *
     * @var string
     */
    public static $csrf_token_time = '__oishy_csrf_token_time';

    /**
     * Get the CSRF Token
     *
     * @param integer $length Token length
     * @return string
     */
    public static function getToken($length = 32)
    {
        $max_time = (int)Config::get('csrf.token_lifetime', 0);
        $stored_time = Session::get(self::$csrf_token_time);
        $csrf_token  = Session::get(self::$csrf_token_key);

        if ($max_time + $stored_time <= time() || empty($csrf_token)) {
            Session::set(self::$csrf_token_key, get_secure_token($length));
            Session::set(self::$csrf_token_time, time());
        }

        return (string)Session::get(self::$csrf_token_key);
    }

    /**
     * Get the CSRF Token, in HTML hidden input field
     *
     * @param string $input_name The name of the input field
     *                           (Optional) Default is self::$csrf_token_key
     *                           by default configuration value is used.
     * @return string
     */
    public static function getTokenHtml($input_name = null)
    {
        if (!$input_name) {
            $input_name = self::$csrf_token_key;
        }
        // Well, just to be safe!
        $input_name = filter_var($input_name, FILTER_SANITIZE_STRING);
        $token = self::getToken();
        $input_name = (string)$input_name;
        $html = sprintf('<input type="hidden" name="%s" value="%s">', $input_name, $token);
        return $html;
    }

    public static function getTokenQuery($input_name = null)
    {
        if (!$input_name) {
            $input_name = self::$csrf_token_key;
        }
        // Well, just to be safe!
        $input_name = filter_var($input_name, FILTER_SANITIZE_STRING);
        $token = self::getToken();
        $input_name = (string)$input_name;
        $str = $input_name . '=' . $token;
        return $str;
    }

    /**
     * Verify the CSRF token against CSRF token submitted via form
     *
     * @param string $input CSRF token submitted via form
     * @return boolean
     */
    public static function verify($input = '')
    {
        if ($input === '') {
            // Try from Post
            if (Request::post(self::$csrf_token_key)) {
                $input = Request::post(self::$csrf_token_key);
            } elseif (Request::get(self::$csrf_token_key)) {
                $input = Request::get(self::$csrf_token_key);
            }
        }
        return $input === Session::get(self::$csrf_token_key) && !empty($input);
    }

    /**
     * Force reset the CSRF token
     *
     * @return boolean
     */
    public static function reset()
    {
        // Remove the value from session
        Session::remove(self::$csrf_token_key);
        // Generate again! -_-
        self::getToken();
        return true;
    }
}
