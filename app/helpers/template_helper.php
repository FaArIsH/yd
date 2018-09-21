<?php
use Oishy\Driver\BreadCrumbs;
use Oishy\Driver\Items;
use Oishy\Model\OptionModel;
use Oishy\Model\PermalinkModel;
use Oishy\Model\TagModel;
use Oishy\Model\VideoModel;

function mi_head()
{
    $title = OptionModel::get('site_name') . ' - ' . OptionModel::get('site_tagline');
    if (registry_get('page.title')) {
        $title = registry_get('page.title') . ' - ' . OptionModel::get('site_name');
    }
    $title = filter_html_escape($title, false);

    $desc = OptionModel::get('site_desc');

    if (registry_get('page.desc')) {
        $desc = registry_get('page.desc');
    }

    $desc = filter_html_escape($desc, false);

    $og_image = base_url('assets/img/og-image.jpg');

    if (registry_get('page.og_image')) {
        $og_image = registry_get('page.og_image');
    }

    $og_image = filter_html_escape($og_image, false);

    echo '<meta charset="utf-8">';
    echo "\n";
    echo "\t" . '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">';
    echo "\n";
    echo "\t" . '<meta http-equiv="x-ua-compatible" content="ie=edge"/>';
    echo "\n";
    echo "\t" . '<title itemprop="name">' . $title . '</title>';
    echo "\n";
    echo "\t" . '<meta name="description" itemprop="description" content="' . $desc . '">';
    echo "\n";
    if (registry_get('page.noindex') === true) {
        echo "\t" . '<!-- Hey Robots! Be respectful, will you? -->';
        echo "\n";
        echo "\t" . '<meta name="robots" content="noindex, nofollow">';
        echo "\n";
    }
    echo "\t" . '<!-- OpenGraph Tags-->';
    echo "\n";
    echo "\t" . '<meta property="og:title" content="' . $title . '">';
    echo "\n";
    echo "\t" . '<meta property="og:type" content="website">';
    echo "\n";
    echo "\t" . '<meta property="og:url" content="' . filter_html_escape(get_current_url(), false) . '">';
    echo "\n";
    echo "\t" . '<meta property="og:image" content="' . $og_image . '">';
    echo "\n";
    echo "\t<!-- Custom Header Scripts -->\n";
    echo "\t". get_option('header_scripts') . "\n";
}

function title_case($string)
{
    $string = str_replace('-', ' ', $string);
    return ucwords($string);
}

function html_schema()
{
    $type = 'page';

    if (registry_get('page.type')) {
        $type = registry_get($type);
    }

    $schema = 'http://schema.org/';

       // Is single post
    if ($type == 'home') {
        $type = "Website";
    } else {
        $type = 'WebPage';
    }

    echo 'itemscope="itemscope" itemtype="' . $schema . $type . '"';
}

function get_siteinfo($type)
{
    $option = "site_{$type}";
    return OptionModel::get($option);
}

function siteinfo($type)
{
    echo filter_html_escape(get_siteinfo($type));
}

function get_categories()
{
    return [
        '1' => 'Film & Animation',
        '2' => 'Cars & Vehicles',
        '10' => 'Music',
        '15' => 'Pets & Animals',
        '17' => 'Sports',
        '19' => 'Travel & Events',
        '20' => 'Gaming',
        '22' => 'People & Blogs',
        '23' => 'Comedy',
        '24' => 'Entertainment',
        '25' => 'News & Politics',
        '26' => 'How-to & Style',
        '27' => 'Education',
        '28' => 'Science & Technology',
        '29' => 'Non-profits & Activism',
    ];
}

function mi_list_categories(array $args = [])
{
    $defaults = [
        'before' => '<li%active%>',
        'after' => '</li>',
        'active_class' => ' active',
        'echo' => true
    ];

    $options = array_merge($defaults, $args);

    $cats = get_categories();

    $html = '';
    foreach ($cats as $id => $name) {
        $class = '';
        if (current_category($id)) {
            $class = $options['active_class'];
        }
        $html .= strtpl($options['before'], ['active' => $class]);
        $link = PermalinkModel::getCategoryPermaLink($id, $name);
        $html .= '<a href="' . $link . '">' . $name . '</a>';
        $html .= strtpl($options['after'], ['active' => $class]) . "\n\t";
    }
    if (!$options['echo']) {
        return $html;
    }
    echo $html;
}



function get_search_post_url()
{
    return base_url('ParseSearchRequest');
}

function search_post_url()
{
    echo get_search_post_url();
}

function get_login_url()
{
    return base_url('dashboard/auth');
}

function login_url()
{
    echo get_login_url();
}

function is_home()
{
    return registry_get('page.is_home', false);
}

function current_category($cat_id)
{
    $current_cat = registry_get('page.category_id', '');
    return $current_cat == $cat_id && !empty($current_cat);
}

function is_category()
{
    return registry_get('page.is_category', false);
}


function get_breadcrumbs($before = '', $after = '')
{
    return BreadCrumbs::getHtml($before, $after);
}

function the_breadcrumbs()
{
    echo get_breadcrumbs();
}


function get_option($name, $default = false)
{
    return OptionModel::get($name, $default);
}

function option($name, $default = false)
{
    echo filter_html_escape(get_option($name, $default));
}

function get_search_query()
{
    return registry_get('search.query');
}

function the_search_query()
{
    echo get_search_query();
}


function get_random_value(array $array, $fallback = false)
{
    if (empty($array)) {
        return $fallback;
    }
    $rand = array_rand($array, 1);
    return $array[$rand];
}

function has_videos()
{
    return registry_get('total_videos', 0) > 0;
}

function videos(array $videos = [])
{
    if (empty($videos)) {
        $videos = registry_get('videos');
    }

    if ($result = $videos->valid()) {
        // register single video
        registry_set('single', $videos->current());
        // move to next
        $videos->next();
    } else {
        $videos->rewind();
    }
    return $result;
}

/**
 * Related videos should be only called after the single  loop
 *
 * @return boolean
 */
function related_videos()
{
    static $loaded = false;
    if (!$loaded) {
        $id = get_the_id();
        $related = VideoModel::relatedVideos($id);
        $videos = new Items($related);
        registry_set('videos', $videos);
        $loaded = true;
    }

    $videos = registry_get('videos');

    if ($result = $videos->valid()) {
        // register single video
        registry_set('single', $videos->current());
        // move to next
        $videos->next();
    } else {
        $videos->rewind();
    }
    return $result;
}


function has_recent_tags()
{
    return TagModel::getTotalTagCount() > 0;
}

function recent_tags($start = 0, $limit = 6)
{
    $start = (int)$start;
    $limit = (int)$limit;
    $tags = registry_get("recent_tags_{$start}_{$limit}");
    if (!$tags) {
        $tags = TagModel::listTags($start, $limit);
        $tags = new Items($tags);
        registry_set("recent_tags_{$start}_{$limit}", $tags);
    }

    if ($result = $tags->valid()) {
        // register single tag
        registry_set('single_tag', $tags->current());
        // move to next
        $tags->next();
    } else {
        $tags->rewind();
    }
    return $result;
}

function get_tag_name()
{
    return registry_get('single_tag.query', '');
}

function the_tag_name()
{
    echo filter_html_escape(get_tag_name());
}

function get_tag_permalink()
{
    return PermalinkModel::getSearchPermalink(get_tag_name());
}

function the_tag_permalink()
{
    echo get_tag_permalink();
}

function get_the_title()
{
    return registry_prop('single', 'snippet.title');
}

function the_title()
{
    echo filter_html_escape(get_the_title(), false);
}

function get_the_ID()
{
    $id = registry_prop('single', 'id.videoId');
    if ($id) {
        return $id;
    }
    return registry_prop('single', 'id');
}

function the_ID()
{
    echo get_the_ID();
}

function get_the_permalink()
{
    $id = get_the_ID();
    $title = get_the_title();
    return PermalinkModel::getSinglePermalink($id, $title);
}

function the_permalink()
{
    echo get_the_permalink();
}


function get_the_download_link()
{
    $id = base64_url_encode(get_the_ID());
    return base_url('/save/' . $id);
}

function the_download_link()
{
    echo get_the_download_link();
}

function get_the_description()
{
    return registry_prop('single', 'snippet.description');
}

function the_description()
{
    echo nl2br(filter_html_escape(get_the_description(), false));
}

function get_the_tags()
{
    return registry_get('single_tags', []);
}

function get_the_player($width = 0, $height = 0, $autoplay = false)
{
    $width = (int)$width;
    $height = (int)$height;
    $size = ' ';
    $query = get_the_ID() . '?autohide=1';
    if ($autoplay) {
        $query .= '&autoplay=1';
    }

    if ($width && $height) {
        $size = ' width="' . $width . '" height="' . $height . '" ';
    }
    return '<iframe'.$size.'src="//www.youtube.com/embed/' . $query . '" frameborder="0" allowfullscreen></iframe>';
}

function the_player($width = 0, $height = 0, $autoplay = false)
{
    echo get_the_player($width, $height, $autoplay);
}

function get_the_thumbnail($size = 'default', $fallback = null)
{
    $size = trim($size);
    return registry_prop('single', 'snippet.thumbnails.' . $size . '.url');
}

function the_thumbnail($size = 'default')
{
    echo get_the_thumbnail($size);
}

function get_timestamp()
{
    $date = registry_prop('single', 'snippet.publishedAt');
    $date = str_replace('T', ' ', $date);
    $date = str_replace('.000Z', '', $date);
    return strtotime($date);
}

function get_the_date($format = 'd-m-Y h:i:s A')
{
    return date($format, get_timestamp());
}

function the_date($format = 'd-m-Y h:i:s A')
{
    echo get_the_date($format);
}

function get_the_channel()
{
    return registry_prop('single', 'snippet.channelTitle');
}

function the_channel()
{
    echo filter_html_escape(get_the_channel(), false);
}

function get_the_channelID()
{
    return registry_prop('single', 'snippet.channelId');
}

function the_channelID()
{
    echo get_the_channelID();
}


function get_comments_count()
{
    $comments = registry_prop('single', 'statistics.commentCount', 0);
    return number_format($comments);
}

function get_favorites_count()
{
    $favorites = registry_prop('single', 'statistics.favoriteCount', 0);
    return number_format($favorites);
}

function get_views_count()
{
    $views = registry_prop('single', 'statistics.viewCount', 0);
    return number_format($views);
}

function get_likes_count()
{
    $likes = registry_prop('single', 'statistics.likeCount', 0);
    return number_format($likes);
}

function get_dislikes_count()
{
    $dislikes = registry_prop('single', 'statistics.dislikeCount', 0);
    return number_format($dislikes);
}

function get_the_channel_permalink()
{
    $id = get_the_channelID();
    $name = get_the_channel();
    return PermalinkModel::getChannelPermalink($id, $name);
}

function the_channel_permalink()
{
    echo get_the_channel_permalink();
}

function get_search_permalink($query, $page = null)
{
    return PermalinkModel::getSearchPermalink($query, $page);
}

function the_search_permalink($query, $page = null)
{
    echo get_search_permalink($query, $page);
}

function get_the_result_count()
{
    return number_format(registry_get('result.total_count', 0));
}

function the_result_count()
{
    echo get_the_result_count();
}

function get_the_pagination($before = '', $after = '')
{
    $prev = registry_get('result.prev_page_token', null);
    $next = registry_get('result.next_page_token', null);
    $html = $before;
    if ($prev) {
        $link = filter_html_escape(get_paged_link($prev), false);
        $html .= '<li class="prev"><a href="' . $link . '" rel="previous">Prev.</a></li>';
    }

    if ($next) {
        $link = filter_html_escape(get_paged_link($next), false);
        $html .= '<li class="next"><a href="' . $link . '" rel="next">Next</a></li>';
    }
    $html .= $after;
    return $html;
}

function the_pagination($before = '', $after = '')
{
    echo get_the_pagination($before, $after);
}

function get_paged_link($page)
{
    $url = get_current_url();
    // remove query strings
    $url = preg_replace('|\?.*?$|', '', $url);
    // Remove existing page tokens, we need it clean!
    $url = preg_replace('|page/[\w-_]*$|', '', $url);
    $url = trailingslashit($url) . 'page/' . $page;
    // append query string aka GET parameters
    if (!empty($_GET)) {
        $url .= '/?' . http_build_query($_GET);
    }
    return $url;
}

function is_search()
{
    return registry_get('page.is_search', false);
}

function get_search_slug()
{
    return registry_get('search.query_slug', '');
}

function set_page_title($title)
{
    return registry_set('page.title', $title);
}

function set_page_desc($desc)
{
    return registry_set('page.desc', $desc);
}

function trim_words($text, $word_limit = 50)
{
    $text = explode(' ', $text, $word_limit);
    if (count($text) >= $word_limit) {
        array_pop($text);
        $text = implode(" ", $text).'...';
    } else {
        $text = implode(" ", $text);
    }
    $text = preg_replace('`\[[^\]]*\]`', '', $text);
    return $text;
}

function is_channel()
{
    return registry_get('page.is_channel', false);
}

function get_channelinfo($key)
{
    return registry_get("channel.{$key}", '');
}

function channelinfo($key)
{
    echo filter_html_escape(get_channelinfo($key), false);
}

function get_the_category_name()
{
    return registry_get('page.category_name', '');
}

function the_category_name()
{
    echo filter_html_escape(get_the_category_name(), false);
}

function get_the_category_id()
{
    return registry_get('page.category_id', 0);
}

function the_category_id()
{
    echo (int)get_the_category_id();
}

function is_single()
{
    return registry_get('page.is_single', false);
}


function get_active_theme()
{
    if (is_mobile()) {
        return get_option('theme_mobile');
    }
    return get_option('theme_desktop');
}

function get_theme_dir_path()
{
    $theme_base = dirslashit(config_get('template.directory'));
    $theme_base .= dirslashit(get_active_theme());
    return $theme_base;
}

function get_theme_dir_url()
{
    $theme_dir = basename(config_get('template.directory'));
    $active_theme = get_active_theme();
    return base_url($theme_dir . '/' . $active_theme . '/');
}

function theme_url($path = '')
{
    $path = ltrim($path, '/');
    return get_theme_dir_url() . $path;
}

function get_theme_logo_url()
{
    if ($logo = get_option('theme_logo')) {
        return base_url('assets/uploads/' . $logo);
    }
    return '';
}

function theme_logo_url()
{
    echo filter_html_escape(get_theme_logo_url(), false);
}

function the_explore_link()
{
    echo PermalinkModel::getSearchPermalink(get_search_query(), 'CAYQAA');
}

function adcode_header()
{
    echo OptionModel::get('adcode_header', '');
}


function adcode_single()
{
    echo OptionModel::get('adcode_single', '');
}

function adcode_download()
{
    echo OptionModel::get('adcode_download', '');
}
