<?php

namespace Oishy\Driver;

use Oishy\Core\Arr;

/**
 * Registry Driver for DoraCMS
 *
 * @package DoraCMS
 * @since 0.1
 * @version 0.1
 */
class Registry
{
    private static $data = [];

    private static $read_only = [];

    public static function get($key, $default = null)
    {
        return Arr::get(self::$data, $key, $default);
    }

    public static function prop($object, $key, $default = null)
    {
        if ($obj = self::get($object)) {
            return array_reduce(explode('.', $key), function ($o, $p) use ($default) {
                if (is_object($o) && property_exists($o, $p)) {
                    return $o->$p;
                }
                return $default;
            }, $obj);
        }

        return $default;
    }

    public static function set($key, $value, $read_only = false)
    {
        // Sorry cap!
        if (in_array($key, self::$read_only)) {
            return false;
        }

        if ($read_only) {
            self::$read_only[] = $key;
        }
        return Arr::set(self::$data, $key, $value);
    }

    public static function remove($key)
    {
        return Arr::erase(self::$data, $key);
    }

    public static function has($key)
    {
        return isset(self::$data[$key]);
    }
}
