<?php
namespace Oishy\Core;

/**
* Oishy - The Divine Atomic PHP Framework!
*
* The Session Wrapper Class
*
* @version 1.0
* @since 0.1
*/

class Session
{

    /**
     * Start the session if not started already
     *
     * @return
     */
    public static function start()
    {
        if (session_id() == '') {
            session_start();
        }
    }

    /**
     * Close the session
     *
     * @return
     */
    public static function close()
    {
        session_write_close();
    }

    /**
     * Set a key, value to session array
     *
     * @param mixed $key   The Key
     * @param mixed $value The Value
     */
    public static function set($key, $value = '')
    {
        Arr::set($_SESSION, $key, $value);
    }

    /**
     * Get a value from session array by its key
     *
     * @param  mixed  $key     The key
     * @param  mixed  $default Fall-back/default value to return
     * @return mixed
     */
    public static function get($key, $default = false)
    {
        return Arr::get($_SESSION, $key, $default);
    }

    /**
     * Unset a session key
     *
     * @param  mixed $key The key
     * @return
     */
    public static function remove($key)
    {
        Arr::erase($_SESSION, $key);
    }

    /**
     * Destroy the session completely
     *
     * @return
     */
    public static function destroy()
    {
        session_destroy();
    }

    /**
     * Regenerate the session ID
     *
     * @param boolean $destroy Whether to destroy the session or not.
     *                         Defaults to FALSE
     * @return
     */
    public static function regenerate($destroy = false)
    {
        session_regenerate_id($destroy);
    }
}
