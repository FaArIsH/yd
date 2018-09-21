<?php
namespace Oishy\Core;

use PDO;
use Slim\PDO\Database;

class Db
{
    protected $where;

    /**
     * PDO database instance
     *
     * @var object
     */
    private static $database = null;

    /**
     * Connection options
     *
     * @var array
     */
    private static $conn_opts = [];

    final private function __construct()
    {
    }

    final private function __clone()
    {
    }

    final private function __wakeup()
    {
    }

    /**
     * Override the connection options ( defined in app/config/db.php )
     *
     * @param array $conn_opts See the array structure from the config file
     */
    public static function setConnOpts(array $conn_opts = [])
    {
        self::$conn_opts = $conn_opts;
        return new static();
    }

    /**
     * Get the PDO connection object
     *
     * @return Get the database instance
     */
    private static function getConn()
    {
        if (!self::$database) {
            $defaults = [
            'dsn' => Config::get('db.dsn'),
            'username' => Config::get('db.username'),
            'password' => Config::get('db.password'),
            'fetch_mode' => Config::get('db.fetch_mode'),
            'err_mode' => Config::get('db.err_mode')
            ];
            $conn_opts = array_merge($defaults, self::$conn_opts);
            $options = [
                PDO::ATTR_DEFAULT_FETCH_MODE => $conn_opts['fetch_mode'],
                PDO::ATTR_ERRMODE => $conn_opts['err_mode']
                ];
            self::$database = new Database(
                $conn_opts['dsn'],
                $conn_opts['username'],
                $conn_opts['password'],
                $options
            );
        }

        return self::$database;
    }


    /**
     * Handle all PDO calls as static method
     *
     * @param  string $method
     * @param  mixed $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return call_user_func_array([self::getConn(), $method], $args);
    }
}
