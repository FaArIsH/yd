<?php

namespace Oishy\Driver;

/**
* BreacCrumbs Driver
*
*/
class BreadCrumbs
{
    protected static $links = [];

    public static function add($label, $link)
    {
        self::$links[$label] = $link;
        return new self();
    }

    public static function getHtml($before = '', $after = '')
    {
        $links = self::$links;

        if (!is_array($links) || empty($links)) {
            return '';
        }

        $total = count($links);

        if ($total < 2) {
            return '';
        }

        if ($before === '' && $after === '') {
            $before = '<ul class="breadcrumb">';
            $after = '</ul>';
        }

        $breadcrumbs = '';
        $breadcrumbs .= $before;
        $i = 1;

        foreach ($links as $label => $link) {
            $label = filter_html_escape($label);
            $link = filter_html_escape($link);

            if ($i === $total) {
                $breadcrumbs .= '<li class="breadcrumb-item active">' . $label . '</li>';
            } else {
                $breadcrumbs .= '
                <li class="breadcrumb-item" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
                <a href="' . $link . '" itemprop="url">
                <span itemprop="title">' . $label . '</span></a>
                </li>';
            }

            $i++;
        }
        $breadcrumbs .= $after;
        return $breadcrumbs;
    }
}
