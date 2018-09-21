<?php
// xD
defined('ROOT') or exit('xD');

use Oishy\Core\Arr;
use Oishy\Core\Alert;
use Oishy\Core\Captcha;
use Oishy\Core\Config;
use Oishy\Core\Cookie;
use Oishy\Core\Csrf;
use Oishy\Core\Encryption;
use Oishy\Core\FileCache;
use Oishy\Core\Filter;
use Oishy\Core\Session;
use Oishy\Core\Response;
use Oishy\Core\Request;
use Oishy\Model\OptionModel;
use Oishy\Model\InstallerModel;
use Oishy\Driver\Registry;

/** Alert Helpers */
function alert_add($type, $messages)
{
    return Alert::add($type, $messages);
}

function alert_read($type, $erase = true)
{
    return Alert::read($type, $erase);
}

function alert_show(
    $type,
    $before = '<div class="toast toast-%type%">',
    $after = '</div>'
) {
    return Alert::show($type, $before, $after);
}

function alert_add_type($type)
{
    return Alert::addType($type);
}

/** Array Helpers */
function array_get($array, $key, $fallback = null)
{
    return Arr::get($array, $key, $fallback = null);
}

function array_set(&$array, $key, $value)
{
    return Arr::set($array, $key, $value);
}

function array_erase(&$array, $key)
{
    return Arr::erase($array, $key);
}

/** Captcha Helpers */
function captcha_render()
{
    return Captcha::render();
}

function captcha_verify($input, $case_sensitive = '')
{
    return Captcha::verify($input, $case_sensitive);
}

/** Config Helpers */
function config_set($key, $value = '')
{
    return Config::set($key, $value);
}

function config_get($key, $default = false)
{
    return Config::get($key, $default);
}

/** Cookie Helpers */
function cookie_set(
    $name,
    $value = '',
    $expire = 0,
    $path = '',
    $domain = '',
    $secure = '',
    $httponly = ''
) {
    return Cookie::set($name, $value, $expire, $path, $domain, $secure, $httponly);
}

function cookie_get($name, $fallback = false)
{
    return Cookie::get($name, $fallback = false);
}

function cookie_remove($name)
{
    return Cookie::remove($name);
}

function cookie_flush()
{
    return Cookie::flush();
}

/** CSRF Helpers */
function csrf_get_token($length = 32)
{
    return Csrf::getToken($length);
}

function csrf_get_token_html($input_name = null)
{
    return Csrf::getTokenHtml($input_name);
}

function csrf_get_query($input_name = null)
{
    return Csrf::getTokenQuery($input_name);
}

function csrf_verify($input = '')
{
    return Csrf::verify($input);
}

/** Encryption Helper */
function oishy_encrypt($plain)
{
    return Encryption::encrypt($plain);
}

function oishy_decrypt($ciphertext)
{
    return Encryption::decrypt($ciphertext);
}

/** File Cache Helpers */
function filecache_save($id, $data, $lifetime = 3600)
{
    return FileCache::save($id, $data, $lifetime);
}

function filecache_get($id)
{
    return FileCache::get($id);
}

function filecache_delete($id)
{
    return FileCache::delete($id);
}

/** Filter Functions */
function filter_html_escape($input, $double_encode = true)
{
    return Filter::htmlEscape($input, $double_encode);
}

function filter_htmlentities($input, $double_encode = true)
{
    return Filter::htmlEntities($input, $double_encode);
}

function filter_batch($var, $functions = null)
{
    return Filter::batch($var, $functions);
}

/** Registry Helpers */
function registry_set($key, $value = '')
{
    return Registry::set($key, $value);
}

function registry_get($key, $default = false)
{
    return Registry::get($key, $default);
}

function registry_remove($key)
{
    return Registry::remove($key);
}

function registry_prop($object, $key, $default = false)
{
    return Registry::prop($object, $key, $default);
}

function response()
{
    return new Response();
}

/** Request Helpers */
## Unlike others these helper functions aren't prefixed with their class name slug
function get($key, $fallback = false)
{
    return Request::get($key, $fallback);
}

function post($key, $fallback = false)
{
    return Request::post($key, $fallback);
}

function server($key, $fallback = false)
{
    return Request::server($key, $fallback);
}

function is_ajax()
{
    return Request::isAjax();
}

/** Session Helpers */
function oishy_session_start()
{
    return Session::start();
}

function oishy_session_close()
{
    return Session::close();
}

function oishy_session_destroy()
{
    return Session::destroy();
}

function session_set($key, $value = '')
{
    return Session::set($key, $value);
}

function session_get($key, $default = false)
{
    return Session::get($key, $default);
}

function session_remove($key)
{
    return Session::remove($key);
}

function session_regenerate($destroy = false)
{
    return Session::regenerate($destroy);
}

function mi_theme_selector($for = 'theme_desktop', $echo = true)
{
    $themes = OptionModel::getThemeList();
    $current = OptionModel::get($for);
    $html = '';
    foreach ($themes as $theme) {
        $s = '';
        if ($theme === $current) {
            $s = ' selected';
        }
        $html .= '<option value="' . $theme . '"' . $s . '>' . $theme . '</option>' . "\n";
    }
    if (!$echo) {
        return $html;
    }
    echo $html;
}


function is_installed()
{
    return InstallerModel::isInstalled();
}

function is_mobile($useragent = '')
{
    if (!$useragent) {
        $useragent = Request::server('HTTP_USER_AGENT');
    }
    $regex="/(android|samsung|^sam\-|s[cg]h|nokia|phone|pod|pad|tablet|touch|motorola|^mot\-|softbank|foma|docomo|kddi|up\.(browser|link)|";
    $regex.="htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|ios|meego|webos|techfaith|palm|";
    $regex.="blackberry|alcatel|amoi|nexian|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";
    $regex.="series(4|6)0|symbian|smartphone|midp|wap|windows (ce|phone)|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";
    $regex.="jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220|480x640|600x800|";
    $regex.="avantgo|bolt|vodafone|pocket|pdxgw|astel|minimo|plucker|pda|xiino|cricket|silk|zune|";
    $regex.="playstation|fennec|hiptop|maemo|compal|kindle|mmp|p(ixi|re)\/|psp|treo|xda|wireless| mobi";
    $regex.=")/i";
    if (preg_match($regex, mb_strtolower($useragent))) {
        return true;
    }
    return false;
}


function trigger_dmca($name)
{
    Response::status(404)->send();
    global $tpl;
    return $tpl->render('dmca', ['name' => filter_html_escape($name)]);
}

function detect_video_ID($url)
{
    $regex = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';
    $video_id = false;
    if (preg_match($regex, $url, $match)) {
        $video_id = $match[1];
    }
    return $video_id;
}
