<?php

namespace MirazMac\YouFetch;

use MirazMac\YouFetch\Exceptions\ItagsException;
use \ArrayAccess;

/**
* Itags Loader
*
* @author MirazMac <mirazmac@gmail.com>
* @version 0.1 Early Access
* @package MirazMac\YouFetch
*/
class Itags
{
    protected static $itagStorage = [];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    public static function load()
    {
        if (!self::$itagStorage) {
            $iTagsPath = __DIR__ . '/' . 'itags.json';
            if (!is_file($iTagsPath)) {
                throw new ItagsException("Failed to read iTags from {$iTagsPath}");
            }
            $itags = json_decode(file_get_contents($iTagsPath), true);
            if (!$itags) {
                throw new ItagsException("The iTags file is possibly corrputed!");
            }
            self::$itagStorage = $itags;
        }

        return self::$itagStorage;
    }

    public static function getDefaultMedia()
    {
        return
        [
            'extension' => null,
            'type' => null,
            'size' => null,
            'itag' => null,
            'link' => null,
            'dash' => false,
            'video' => [
                '3d' => false,
                'codec' => null,
                'width' => null,
                'height' => null,
                'bitrate' => null,
                'framerate' => null
            ],
            'audio' => [
                'codec' => null,
                'bitrate' => null,
                'frequency' => null
            ]
        ];
    }

    public static function getDefaultInfo()
    {
        return [
            'title' => null,
            'author' => null,
            'uid' => null,
            'video_id' => null,
            'thumbnail_url' => null,
            'length_seconds' => 0,
            'view_count' => 0,
            'keywords' => null,
            'avg_rating' => 0,
        ];
    }
}
