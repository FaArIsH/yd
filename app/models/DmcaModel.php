<?php

namespace Oishy\Model;

/**
*
*/
class DmcaModel
{
    protected static $is_blocked = false;

    public static function channel($channel_id)
    {
        $ids = OptionModel::get('dmca_channel_ids', '');
        self::$is_blocked = self::searchStr($ids, $channel_id);
        return new static();
    }

    public static function single($video_id)
    {
        $ids = OptionModel::get('dmca_ids', '');
        self::$is_blocked = self::searchStr($ids, $video_id);
        return new static();
    }

    public static function search($slug)
    {
        $ids = OptionModel::get('dmca_search_queries', '');
        $ids = explode(',', $ids);
        $ids = array_map('trim', $ids);
        self::$is_blocked = in_array($slug, $ids);
        return new static();
    }

    public static function isBlocked()
    {
        return self::$is_blocked;
    }

    protected static function searchStr(&$string, $search)
    {
        if (empty($string)) {
            return false;
        }
        if (preg_match("/{$search}/i", $string)) {
            return true;
        }
        return false;
    }
}
