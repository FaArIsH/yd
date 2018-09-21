<?php

use MirazMac\YouFetch\YouFetch;
use Oishy\Core\Response;
use Oishy\Core\Router;
use Oishy\Driver\BreadCrumbs;
use Oishy\Driver\Items;
use Oishy\Driver\Registry;
use Oishy\Model\ApiModel;
use Oishy\Model\DmcaModel;
use Oishy\Model\PermalinkModel;
use Oishy\Model\VideoModel;

/**
 * Route for Search Page
 *
 */
Router::map('GET', PermalinkModel::getSearchRoute(), function () use ($tpl) {
    Registry::set('page.is_search', true, true);
    $q = Registry::get('route.params.query', '');
    $q = trim($q);
    $page = Registry::get('route.params.page', '');

    if (empty($q)) {
        return trigger_404_error();
    }

    // Process for title case
    $title = title_case($q);

    if (DmcaModel::search($q)->isBlocked()) {
        return trigger_dmca($title);
    }

    BreadCrumbs::add('Search Results for ' . $title, '');
    Registry::set('page.title', 'Search Results for ' . $title);
    Registry::set('search.query', $title);
    Registry::set('search.query_slug', $q);
    Registry::set('page.desc', 'Search Results of ' . $title . '. Check all videos related to ' . $title . '.');

    // search youtube
    $result = VideoModel::search($q, $page);
    $videos = new Items($result);
    // Update in registry
    Registry::set('total_videos', count($result));
    Registry::set('videos', $videos);
    return $tpl->render(['search', 'archive']);
});

/**
 * Route for Category Page
 *
 */
Router::map('GET', PermalinkModel::getCategoryRoute(), function () use ($tpl) {
    Registry::set('page.is_category', true, true);
    $id = Registry::get('route.params.id', 0);
    $page = Registry::get('route.params.page', '');
    $slug = Registry::get('route.params.slug');
    $cats = get_categories();
    if (!isset($cats[$id])) {
        return trigger_404_error();
    }
    $cat_name = $cats[$id];
    BreadCrumbs::add($cat_name, '');
    Registry::set('page.title', 'All Videos of Category ' . $cat_name);
    Registry::set('page.desc', 'All Videos of Category ' . $cat_name . '. Check all videos related to ' . $cat_name . '.');
    Registry::set('page.category_name', $cat_name);
    Registry::set('page.category_id', $id);

    // search youtube
    $result = VideoModel::searchCategory($id, $page);
    $videos = new Items($result);

    // Update in registry
    Registry::set('total_videos', count($result));
    Registry::set('videos', $videos);

    return $tpl->render(['category', 'archive']);
});

/**
 * Route for Channel
 *
 */
Router::map('GET', PermalinkModel::getChannelRoute(), function () use ($tpl) {
    Registry::set('page.is_channel', true, true);
    $channel_id = Registry::get('route.params.id', '');
    $page = Registry::get('route.params.page', '');
    $channel_info = VideoModel::getChannelInfo($channel_id);

    if (!$channel_info) {
        return trigger_404_error();
    }

    if (DmcaModel::channel($channel_id)->isBlocked()) {
        return trigger_dmca($channel_info->snippet->title);
    }

    foreach ($channel_info->snippet as $key => $value) {
        Registry::set("channel.{$key}", $value);
    }

    Registry::set('channel.id', $channel_info->id);
    Registry::set('page.title', 'All videos by ' . $channel_info->snippet->title);
    Registry::set('page.desc', trim_words($channel_info->snippet->description, 60));
    BreadCrumbs::add('All videos by ' . $channel_info->snippet->title, '');

    // search youtube
    $result = VideoModel::searchChannel($channel_id, $page);
    $videos = new Items($result);

    // Update in registry
    Registry::set('total_videos', count($result));
    Registry::set('videos', $videos);
    return $tpl->render(['channel', 'archive']);
});

/**
 * Route for Single Video
 *
 */
Router::map('GET', PermalinkModel::getSingleRoute(), function () use ($tpl) {
    Registry::set('page.is_single', true, true);
    $id = Registry::get('route.params.id');
    // search youtube
    $result = VideoModel::getSingle($id);

    if (!$result || empty($result)) {
        return trigger_404_error();
    }

    if (DmcaModel::channel($result[0]->snippet->channelId)->isBlocked()) {
        return trigger_dmca($result[0]->snippet->title);
    }

    if (DmcaModel::single($id)->isBlocked()) {
        return trigger_dmca($result[0]->snippet->title);
    }

    $videos = new Items($result);
    // Update in registry
    Registry::set('total_videos', count($result));
    Registry::set('videos', $videos);
    Registry::set('page.title', $result[0]->snippet->title);
    Registry::set('page.desc', trim_words($result[0]->snippet->description, 50));
    Registry::set('page.og_image', $result[0]->snippet->thumbnails->default->url);
    if (isset($result[0]->snippet->tags)) {
        Registry::set('single_tags', $result[0]->snippet->tags);
    }

    $channel_url = PermalinkModel::getChannelPermalink(
        $result[0]->snippet->channelId,
        $result[0]->snippet->channelTitle
    );
    BreadCrumbs::add($result[0]->snippet->channelTitle, $channel_url);
    BreadCrumbs::add($result[0]->snippet->title, get_current_url());

    return $tpl->render(['single', 'archive', 'index']);
});


/**
 * Route for Download Page
 *
 */
Router::map('GET', '/save/[slug:id]', function ($id) use ($tpl) {
    $id = base64_url_decode($id);
    try {
        $downloader = new YouFetch($id);
    } catch (\Exception $e) {
        return trigger_404_error();
    }
    $videoInfo = $downloader->fetchVideoInfo();
    $streamLinks = $downloader->fetchAll();
    $data = [
        'videoInfo' => $videoInfo,
        'streamLinks' => $streamLinks
    ];
    Registry::set('page.title', "Download Links for {$videoInfo['title']}", true);
    Registry::set('page.noindex', true, true);
    return $tpl->render('save', $data);
});
