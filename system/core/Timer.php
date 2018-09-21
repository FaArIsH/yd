<?php
namespace Oishy\Core;

class Timer
{
    /**
     * Start time
     *
     * @var integer
     */
    protected static $start;

    /**
     * Pause time
     *
     * @var integer
     */
    protected static $pause_time;

    /**
     * Constructor to use in non static way
     *
     */
    public function __construct()
    {
        self::start();
    }

    /**
     * Start the timer
     *
     * @return
     */
    public static function start()
    {
        self::$start = self::getTime();
        self::$pause_time = 0;
    }

    /**
     * Pause the timer
     *
     * @return
     */
    public static function pause()
    {
        self::$pause_time = self::getTime();
    }

    /**
     * Resume the timer
     *
     * @return
     */
    public static function resume()
    {
        self::$start += (self::getTime() - self::$pause_time);
        self::$pause_time = 0;
    }

    /**
     * Get the proccesing time in readable format
     *
     * @param  integer $decimals number of decimals to use in round
     * @return mixed
     */
    public static function get($decimals = 8)
    {
        return round((self::getTime() - self::$start), $decimals);
    }

    /**
     * Get the current time
     *
     * @access private
     * @return float
     */
    private static function getTime()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }
}
