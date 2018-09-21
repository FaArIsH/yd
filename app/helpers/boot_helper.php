<?php

use Oishy\Driver\Registry;
use Oishy\Driver\Flash;
use Oishy\Driver\BreadCrumbs;
use Oishy\Core\Config;
use Oishy\Core\Router;
use Oishy\Core\Response;

if (!is_installed()) {
    header("Content-Type: application/json");
    header("Expires: on, 01 Jan 1970 00:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    Response::redirect('install.php')->send(1);
}

/**
 * Template instance
 *
 * @var Flash
 */
$tpl = new Flash(get_theme_dir_path());
/**
 * Admin View Object
 *
 * @var Flash
 */
$view = new Flash(apppath('views'));

// Add home to breadcrumbs
BreadCrumbs::add('Home', base_url());

/**
 * Set 404 error page
 *
 */
Config::set('error.404_callback', function () use ($tpl) {
    Registry::set('page.title', 'Error 404 - Page Not Found');
    Registry::set('page.is_404', true, true);
    $tpl->render('404');
});

/**
 * Add Router Match Types
 *
 */
Router::addMatchTypes([
    'slug' => '[p{L}\p{Nd}\p{M}\s\w@,&+-]++',
    'yid' => '[A-z0-9-_]++',
    'hyphen_number' => '(-[0-9]++)'
]);
