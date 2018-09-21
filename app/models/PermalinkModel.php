<?php

namespace Oishy\Model;

use Oishy\Core\Db;

/**
*
*/
class PermalinkModel
{

    public static function getSearchPermalink($query, $page = null)
    {
        $query = oishy_url_slug($query);
        $link = OptionModel::get('permalink_search');
        $link = strtpl($link, ['query' => $query]);
        if ($page) {
            $link .= '/page/' . filter_html_escape($page);
        }
        return base_url($link);
    }

    public static function getSinglePermalink($id, $title = '')
    {
        $id = trim($id);
        $slug = oishy_url_slug($title);
        $link = OptionModel::get('permalink_single');
        $link = strtpl($link, ['id' => $id, 'slug' => $slug]);
        return base_url($link);
    }

    public static function getDirectSinglePermalink($id)
    {
        $id = filter_html_escape($id);
        return base_url("watch?v={$id}");
    }

    public static function getChannelPermalink($id, $name = '', $page = null)
    {
        $id = trim($id);
        $slug = oishy_url_slug($name);
        $link = OptionModel::get('permalink_channel');
        $link = strtpl($link, ['id' => $id, 'slug' => $slug]);
        if ($page) {
            $link .= '/page/' . filter_html_escape($page);
        }
        return base_url($link);
    }

    public static function getCategoryPermalink($id, $name = '', $page = null)
    {
        $id = trim($id);
        $slug = oishy_url_slug($name);
        $link = OptionModel::get('permalink_category');
        $link = strtpl($link, ['id' => $id, 'slug' => $slug]);
        if ($page) {
            $link .= '/page/' . filter_html_escape($page);
        }
        return base_url($link);
    }

    public static function getSearchRoute()
    {
        $link_base = OptionModel::get('permalink_search');
        $link = strtpl($link_base, ['query' => '[slug:query]']);
        return $link . '[/page/]?[a:page]?';
    }

    public static function getSingleRoute()
    {
        $link_base = OptionModel::get('permalink_single');
        return strtpl($link_base, ['id' => '[yid:id]','slug' => '[slug:slug]']);
    }

    public static function getCategoryRoute()
    {
        $link_base = OptionModel::get('permalink_category');
        $link = strtpl($link_base, ['id' => '[a:id]','slug' => '[slug:slug]']);
        return $link . '[/page/]?[a:page]?';
    }

    public static function getChannelRoute()
    {
        $link_base = OptionModel::get('permalink_channel');
        $link = strtpl($link_base, ['id' => '[slug:id]','slug' => '[slug:slug]']);
        return $link . '[/page/]?[a:page]?';
    }

    public static function formatRoute($link)
    {
        $link = trim($link);
        // remove any trailing slash
        $link = untrailingslashit($link);
        // Make sure we have a leading slash
        $link = '/' . ltrim($link, '/');
        return $link;
    }
}
