<?php

namespace Oishy\Driver;

/**
* Dot
*/
class Dot
{
    protected $cache_dir;
    protected $lifetime;

    /**
     * Create a new Dot instance
     *
     * @param string  $cache_dir Path to the cache directory, will be created if not exist
     * @param integer $lifetime  The default expiration time for cache files, in seconds
     */
    public function __construct($cache_dir, $lifetime = 86400)
    {
        // Create the cache directory if not available
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir, 0755, true);
        }
        // Make sure the cache directory is readable, otherwise we are fucked -_-
        if (!is_readable($cache_dir)) {
            throw new \LogicException("Failed to read directory at: {$cache_dir}");
        }

        // Set the cache directory
        $this->cache_dir = ltrim($cache_dir, '/\\');
        // And the default lifetime
        $this->setLifeTime($lifetime);
    }

    /**
     * Save something to cache
     *
     * @param  string $key  Unique cache identifier key
     * @param  mixed $data The data, currently works with any type of data except `resources`
     * @return boolean
     */
    public function save($key, $data)
    {
        // Instead of using serialize() or json_encode() we use var_export()
        // For huge performance boost in PHP 7+ with opcache enabled
        $data = var_export($data, true);
        $data = str_replace('stdClass::__set_state', '(object) ', $data);

        // Build path to the cache file
        $cache_path = $this->buildFilePath($key);
        // Create a valid PHP file with the data
        $processed_data = '<?php $cache_data = ' . $data . ';';

        return (bool)file_put_contents($cache_path, $processed_data, LOCK_EX);
    }

    /**
     * Get something from cache or delete if it has crossed its expiration time
     *
     * @param  string $key      Unique cache identifier key
     * @param  integer $lifetime The default expiration time for cache file, in seconds. If the cache file is older than
     *                           this the file will be deleted and FALSE will be returned. If left empty self::$lifetime
     *                           will be used instead.
     * @return mixed
     */
    public function get($key, $lifetime = '')
    {
        // If parameter left empty we will use the default lifetime instead
        if ($lifetime === '') {
            $lifetime = $this->lifetime;
        } else {
            $lifetime = (int)$lifetime;
        }

        // Build path to the cache file
        $cache_path = $this->buildFilePath($key);

        // Check if there is actually a cache file with that name
        if (!is_file($cache_path)) {
            return false;
        }
        // Make sure we the file is not older than the provided lifetime
        if (time() - filemtime($cache_path) >= $lifetime) {
            @unlink($cache_path);
            return false;
        }

        // No need to read and decode the data, since we saved it as valid PHP code!
        // Opcode cache, yea!
        include $cache_path;

        if (isset($cache_data)) {
            return $cache_data;
        }

        return false;
    }

    /**
     * Change the default cache lifetime
     *
     * @param integer $lifetime The default expiration time for cache files, in seconds
     */
    public function setLifeTime($lifetime)
    {
        $this->lifetime = (int)$lifetime;
        return $this;
    }

    /**
     * Build the cache file path
     *
     * @param  string $key Unique cache identifier key
     * @return string
     */
    protected function buildFilePath($key)
    {
        $key = trim($key);
        return $this->cache_dir . '/' . sha1($key) . '.tmp';
    }
}
