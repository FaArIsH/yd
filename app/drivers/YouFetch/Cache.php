<?php

namespace MirazMac\YouFetch;

/**
* A Dead Simple File Cache Class
*
* Its purpose is to store the decoded signature.
*
* @author MirazMac <mirazmac@gmail.com>
* @version 0.1 Early Access
* @package MirazMac\YouFetch
*/
class Cache
{
    const FILE_EXTENSION = '.cache';
    const DEFAULT_LIFETIME = 86400;

    protected static $cacheDir = '/tmp/';

    public static function setStoragePath($cacheDir)
    {
        self::$cacheDir = rtrim($cacheDir, '/\\') . '/';

        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir);
        }
    }

    public static function save($id, $data)
    {
        $cachePath = self::getFileName($id);
        return file_put_contents($cachePath, serialize($data), LOCK_EX);
    }

    public static function get($id, $olderThan = null)
    {
        $cachePath = self::getFileName($id);

        if (!is_file($cachePath)) {
            return false;
        }

        if ($olderThan === null) {
            $olderThan = static::DEFAULT_LIFETIME;
        }

        if ($olderThan === false) {
            return unserialize(file_get_contents($cachePath));
        }

        if (time() - filemtime($cachePath) >= $olderThan) {
            @unlink($cachePath);
            return false;
        }

        return unserialize(file_get_contents($cachePath));
    }

    protected static function getFileName($id)
    {
        //return self::$cacheDir . sha1(trim($id)) . self::FILE_EXTENSION;
        return self::$cacheDir . trim($id) . self::FILE_EXTENSION;
    }
}
