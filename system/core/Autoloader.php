<?php
/**
 * Oishy's Core Autoloader Class
 *
 * @since 0.1 Added as a replacement of composer's autoloader
 * @version 1.0
 */
namespace Oishy\Core;

class Autoloader
{
    /**
     * An associative array where the key is a namespace prefix and the value
     * is an array of base directories for classes in that namespace.
     *
     * @var array
     */
    protected static $prefixes = [];

    /**
     * Array of directories to load non-namespaced classes
     *
     * @var array
     */
    protected static $directories = [];

    /**
     * Register loader with SPL autoloader stack.
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'loadClass']);
        return new static();
    }

    /**
     * Adds a base directory for a namespace prefix.
     *
     * @param string $prefix The namespace prefix.
     * @param string $base_dir A base directory for class files in the
     * namespace.
     * @param bool $prepend If true, prepend the base directory to the stack
     * instead of appending it; this causes it to be searched first rather
     * than last.
     * @return void
     */
    public static function addNamespace($prefix, $base_dir, $prepend = false)
    {
        // Well! What if someone tries to add a blank namespace to load nonspaced classes?
        // Maybe not a good design pattern for now, will think about removing later
        if ($prefix == '') {
            return self::addDirectory($base_dir);
        }
        // normalize namespace prefix
        $prefix = trim($prefix, '\\') . '\\';

        // normalize the base directory with a trailing separator
        $base_dir = dirslashit($base_dir);

        // initialize the namespace prefix array
        if (isset(self::$prefixes[$prefix]) === false) {
            self::$prefixes[$prefix] = [];
        }

        // retain the base directory for the namespace prefix
        if ($prepend) {
            array_unshift(self::$prefixes[$prefix], $base_dir);
        } else {
            array_push(self::$prefixes[$prefix], $base_dir);
        }

        return new static();
    }

    /**
     * Add a directory to autoload non-namespaced classes
     *
     * @param string $dir_name A base directory for class files
     */
    public static function addDirectory($dir_name)
    {
        $dir_name = dirslashit($dir_name);
        self::$directories[] = $dir_name;

        // Only unique ones please!
        // Note: I tried array_key_exists() in the first place, but it didn't worked
        self::$directories = array_unique(self::$directories);
        return new static();
    }



    /**
     * Loads the class file for a given class name.
     *
     * @param string $class The fully-qualified class name.
     * @return mixed The mapped file name on success, or boolean false on
     * failure.
     */
    public static function loadClass($class)
    {
        // the current namespace prefix
        $prefix = $class;

        // No namespace found, eh? K, then!
        if (strpos($prefix, '\\') === false) {
            return self::loadNonNamespacedClass($class);
        }

        // work backwards through the namespace names of the fully-qualified
        // class name to find a mapped file name
        while (false !== $pos = strrpos($prefix, '\\')) {
            // retain the trailing namespace separator in the prefix
            $prefix = substr($class, 0, $pos + 1);

            // the rest is the relative class name
            $relative_class = substr($class, $pos + 1);

            // try to load a mapped file for the prefix and relative class
            $mapped_file = self::loadMappedFile($prefix, $relative_class);
            if ($mapped_file) {
                return $mapped_file;
            }

            // remove the trailing namespace separator for the next iteration
            // of strrpos()
            $prefix = rtrim($prefix, '\\');
        }

        // never found a mapped file
        return false;
    }

    /**
     * Loads the class file for a given class name.
     * Only for classes without any namespace in it
     *
     * @param string $class The fully-qualified class name.
     * @return mixed The mapped file name on success, or boolean false on
     * failure.
     */
    public static function loadNonNamespacedClass($class)
    {
        // What's the point? you say watson!
        if (!is_array(self::$directories) || empty(self::$directories)) {
            return false;
        }
        foreach (self::$directories as $base_dir) {
            $file = dirslashit($base_dir) . $class . '.php';
            // if the mapped file exists, require it
            if (self::requireFile($file)) {
                // yes, we're done
                return $file;
            }
        }

        return false;
    }

    /**
     * Load the mapped file for a namespace prefix and relative class.
     *
     * @param string $prefix The namespace prefix.
     * @param string $relative_class The relative class name.
     * @return mixed Boolean false if no mapped file can be loaded, or the
     * name of the mapped file that was loaded.
     */
    protected static function loadMappedFile($prefix, $relative_class)
    {
        // are there any base directories for this namespace prefix?
        if (isset(self::$prefixes[$prefix]) === false) {
            return false;
        }

        // look through base directories for this namespace prefix
        foreach (self::$prefixes[$prefix] as $base_dir) {
            // replace the namespace prefix with the base directory,
            // replace namespace separators with directory separators
            // in the relative class name, append with .php
            $file = $base_dir
                  . str_replace('\\', '/', $relative_class)
                  . '.php';

            // if the mapped file exists, require it
            if (self::requireFile($file)) {
                // yes, we're done
                return $file;
            }
        }

        // never found it
        return false;
    }

    /**
     * If a file exists, require it from the file system.
     *
     * @param string $file The file to require.
     * @return bool True if the file exists, false if not.
     */
    protected static function requireFile($file)
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }
}
