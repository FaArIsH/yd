<?php
namespace Oishy\Core;

/**
 * Simple FileCache Library
 *
 * Slightly modified for Oishy
 * @author  Taiji Inoue <inudog@gmail.com>
 * @version 1.0
 * @since 0.1
 */

class FileCache
{
    /**
     * Fetches an entry from the cache.
     *
     * @param string $id
     */
    public static function get($id)
    {
        $file_name = self::getFileName($id);

        if (!is_file($file_name) || !is_readable($file_name)) {
            return false;
        }

        $lines    = file($file_name);
        $lifetime = array_shift($lines);
        $lifetime = (int) trim($lifetime);

        if ($lifetime !== 0 && $lifetime < time()) {
            @unlink($file_name);
            return false;
        }
        $serialized = join('', $lines);
        $data       = unserialize($serialized);
        return $data;
    }

    /**
     * Deletes a cache entry.
     *
     * @param string $id
     *
     * @return bool
     */
    public static function delete($id)
    {
        $file_name = self::getFileName($id);
        return unlink($file_name);
    }

    /**
     * Puts data into the cache.
     *
     * @param string $id
     * @param mixed  $data
     * @param int    $lifetime
     *
     * @return bool
     */
    public static function save($id, $data, $lifetime = 3600)
    {
        $dir = self::getDirectory($id);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return false;
            }
        }
        $file_name  = self::getFileName($id);
        $lifetime   = time() + $lifetime;
        $serialized = serialize($data);
        $result     = file_put_contents($file_name, $lifetime . PHP_EOL . $serialized);
        if ($result === false) {
            return false;
        }
        return true;
    }

    /**
     * Fetches a directory to store the cache data
     *
     * @param string $id
     *
     * @return string
     */
    protected static function getDirectory($id)
    {
        $hash = sha1($id, false);
        $dirs = [
            self::getCacheDirectory(),
            mb_substr($hash, 0, 2),
            mb_substr($hash, 2, 2)
        ];
        return join(DS, $dirs);
    }

    /**
     * Fetches a base directory to store the cache data
     *
     * @return string
     */
    protected static function getCacheDirectory()
    {
        return dirslashit(Config::get('cache.directory'));
    }

    /**
     * Fetches a file path of the cache data
     *
     * @param string $id
     *
     * @return string
     */
    protected static function getFileName($id)
    {
        $directory = self::getDirectory($id);
        $hash      = sha1($id, false);
        $file      = $directory . $hash . '.' . Config::get('cache.extension', 'cache');
        return $file;
    }
}
