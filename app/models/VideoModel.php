<?php

namespace Oishy\Model;

use Oishy\Core\Db;
use Oishy\Core\Cookie;
use Oishy\Core\Request;
use Oishy\Driver\Registry;
use Oishy\Driver\Xinia;
use Oishy\Driver\Dot;

/**
*
*/
class VideoModel
{

    public static function parseSearch()
    {
        $query = trim(Request::post('q', ''));
        // What kinda sorcery is this? *_*
        if (empty($query)) {
            return base_url();
        }
        // Try to get the video ID
        $video_id = detect_video_ID($query);
        if ($video_id) {
            return PermalinkModel::getSinglePermalink($video_id, 'direct-download');
        }

        // Store the query
        TagModel::add($query);

        return PermalinkModel::getSearchPermalink($query);
    }

    public static function search($q, $page = '')
    {
        $api_key = ApiModel::getApiKey();
        if (!$api_key) {
            return [];
        }
        $items_per_page = (int)OptionModel::get('videos_per_page');
        $youtube = new Xinia($api_key);

        // Set Default Parameters
        $params = [
            'q'             => $q,
            'type'          => 'video',
            'part'          => 'id, snippet',
            'maxResults'    => $items_per_page,
            'safeSearch'    => 'none'
        ];

        // Add Page Token if has any
        if (!empty($page)) {
            $params['pageToken'] = $page;
        }

        $search = $youtube->search($params);
        Registry::set('result.total_count', $youtube->getStat('totalResults', 0));
        Registry::set('result.prev_page_token', $youtube->getStat('prevPageToken'));
        Registry::set('result.next_page_token', $youtube->getStat('nextPageToken'));

        return $search;
    }

    public static function getChannelInfo($id)
    {
        $api_key = ApiModel::getApiKey();
        if (!$api_key) {
            return false;
        }
        $youtube = new Xinia($api_key);
        return $youtube->getChannelInfo($id);
    }

    public static function searchChannel($channel_id, $page = '')
    {
        $api_key = ApiModel::getApiKey();
        if (!$api_key) {
            return [];
        }
        $items_per_page = (int)OptionModel::get('videos_per_page');
        $youtube = new Xinia($api_key);

        // Set Default Parameters
        $params = [
            'type'          => 'video',
            'channelId'     => $channel_id,
            'part'          => 'id,snippet',
            'maxResults'    => $items_per_page,
            'safeSearch'    => 'none'
        ];

        // Add Page Token if has any
        if (!empty($page)) {
            $params['pageToken'] = $page;
        }

        $search = $youtube->search($params);

        Registry::set('result.total_count', $youtube->getStat('totalResults', 0));
        Registry::set('result.prev_page_token', $youtube->getStat('prevPageToken'));
        Registry::set('result.next_page_token', $youtube->getStat('nextPageToken'));

        return $search;
    }

    public static function searchCategory($id, $page = '')
    {
        $api_key = ApiModel::getApiKey();
        if (!$api_key) {
            return [];
        }
        $items_per_page = (int)OptionModel::get('videos_per_page');
        $youtube = new Xinia($api_key);

        // Set Default Parameters
        $params = [
            'type'          => 'video',
            'videoCategoryId'     => $id,
            'part'          => 'id,snippet',
            'maxResults'    => $items_per_page,
            'safeSearch'    => 'none'
        ];

        // Add Page Token if has any
        if (!empty($page)) {
            $params['pageToken'] = $page;
        }

        $search = $youtube->search($params);

        Registry::set('result.total_count', $youtube->getStat('totalResults', 0));
        Registry::set('result.prev_page_token', $youtube->getStat('prevPageToken'));
        Registry::set('result.next_page_token', $youtube->getStat('nextPageToken'));

        return $search;
    }

    public static function getSingle($id)
    {

        $api_key = ApiModel::getApiKey();
        if (!$api_key) {
            return false;
        }

        $youtube = new Xinia($api_key);

        // Set Default Parameters
        $params = [
            'type'          =>  'video',
            'id'            =>  $id,
            'part'          =>  'id, snippet, contentDetails, statistics',
            'safeSearch'    =>  'none'
        ];

        $search = $youtube->search($params, 'videos');

        Registry::set('result.total_count', $youtube->getStat('totalResults', 0));
        Registry::set('result.prev_page_token', $youtube->getStat('prevPageToken'));
        Registry::set('result.next_page_token', $youtube->getStat('nextPageToken'));

        return $search;
    }

    public static function relatedVideos($videoId)
    {
        $api_key = ApiModel::getApiKey();
        if (!$api_key) {
            return [];
        }
        $items_per_page = (int)OptionModel::get('related_videos', 4);
        $youtube = new Xinia($api_key);

        // Set Default Parameters
        $params = [
            'type'          => 'video',
            'relatedToVideoId' => $videoId,
            'part'          => 'id,snippet',
            'maxResults'    => $items_per_page,
            'safeSearch'    => 'none'
        ];

        // Add Page Token if has any
        if (!empty($page)) {
            $params['pageToken'] = $page;
        }

        $search = $youtube->search($params);

        Registry::set('result.related_count', $youtube->getStat('totalResults', 0));

        return $search;
    }

    public static function getRandQuery()
    {
        $queries = OptionModel::get('homepage_queries', '');
        $queries = explode(',', $queries);
        $queries = array_map('trim', $queries);
        $key = array_rand($queries);
        return $queries[$key];
    }
}
