<?php

namespace MirazMac\YouFetch;

use \Requests_Session;
use \Requests_Cookie_Jar;

/**
* Requests Http Wrapper
*
* @author MirazMac <mirazmac@gmail.com>
* @version 0.1 Early Access
* @package MirazMac\YouFetch
*/
class Http
{
    protected static $session;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    public static function getSession()
    {
        if (!static::$session) {
            // Eww, I hate PSR-0
            static::$session = new Requests_Session;
            // Maybe YouTube would bless us, if we use chrome? idk xD
            static::$session->useragent =
            'Mozilla/5.0 (Windows NT 6.3; WOW64) ' .
            'AppleWebKit/537.36 (KHTML, like Gecko) ' .
            'Chrome/40.0.2214.115 Safari/537.36';
            static::$session->headers['Accept'] =
            'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
            static::$session->headers['Accept-Language'] = 'en-US,en;q=0.5';
            // Obvious -_-
            static::$session->headers['Referer'] = 'https://youtube.com';
            // Not works, still will try
            if (isset($_SERVER['REMOTE_ADDR'])) {
                static::$session->headers['X-Forwarded-For'] = $_SERVER['REMOTE_ADDR'];
            }
            static::$session->options['timeout'] = 100;
            static::$session->options['connect_timeout'] = 100;
            // This will make us look like more less of an asshole to YouTube
            static::$session->options['cookies'] = new Requests_Cookie_Jar;
        }

        return self::$session;
    }
}
