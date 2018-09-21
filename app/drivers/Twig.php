<?php

namespace Oishy\Driver;

use Oishy\Core\Config;

/**
*
*/
class Twig
{
    protected static $instance = null;

    private function __construct()
    {
    }

    private function __wakeup()
    {
    }

    private function __clone()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            $cache = syspath('cache');
            $debug = false;
            // Enable debug mode for developers
            if (is_dev()) {
                $debug = true;
            }
            $twig_options = [
                'cache' => $cache,
                'autoescape' => false,
                'debug' => $debug,
                'auto_reload' => true
            ];
            $twig_loader = new \Twig_Loader_Filesystem(Config::get('template.directory'));
            $twig = new \Twig_Environment($twig_loader, $twig_options);
        }
    }
}
