<?php
namespace Oishy\Core;

/**
 * Configuration file reader class
 *
 * @since 0.1
 * @version 1.0
 */

class Config
{

    /**
     * Holds the app data
     *
     * @var array
     */
    public static $config_data = [];

    /**
     * Returns a value from the config array
     *
     * @param string
     * @param mixed
     * @return mixed
     */
    public static function get($key, $fallback = null)
    {
        // first segment refers to config file
        $keys = explode('.', $key);
        ;
        // read the config file if we have one
        if (!array_key_exists($file = current($keys), self::$config_data)) {
            if (is_readable($path = apppath('config/' . $file . '.php'))) {
                self::$config_data[$file] = require $path;
            }
        }

        return Arr::get(self::$config_data, $key, $fallback);
    }

    /**
     * Sets a value in the config array
     *
     * @param string
     * @param mixed
     */
    public static function set($key, $value)
    {
        Arr::set(self::$config_data, $key, $value);
    }

    /**
     * Removes value in the config array
     *
     * @param string
     */
    public static function erase($key)
    {
        Arr::erase(self::$config_data, $key);
    }

    /**
     * Returns a value from the config array using the
     * method call as the file reference
     *
     * @example Config::app('url');
     *
     * @param string
     * @param array
     */
    public static function __callStatic($method, $arguments)
    {
        $key = $method;
        $fallback = null;

        if (count($arguments)) {
            $key .= '.' . array_shift($arguments);
            $fallback = array_shift($arguments);
        }

        return self::get($key, $fallback);
    }
}
