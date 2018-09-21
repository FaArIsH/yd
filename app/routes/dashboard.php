<?php

use MirazMac\Pagination\Pagination;
use Oishy\Core\Alert;
use Oishy\Core\Csrf;
use Oishy\Core\Response;
use Oishy\Core\Router;
use Oishy\Core\Session;
use Oishy\Driver\BreadCrumbs;
use Oishy\Driver\Registry;
use Oishy\Model\ApiModel;
use Oishy\Model\AuthModel;
use Oishy\Model\DashModel;
use Oishy\Model\OptionModel;
use Oishy\Model\PermalinkModel;

/**
 * Route for login page
 *
 */
Router::map('GET', '/dashboard/login', function () use ($view) {
    Session::start();
    Registry::set('page.is_login_page', true, true);

    // No need to login again
    if (AuthModel::isLogged()) {
        return Response::redirect('dashboard', true, 301)->send(1);
    }

    BreadCrumbs::add('Log In', base_url('dashboard/login'));
    Registry::set('page.title', 'Log In');

    return $view->render('login');
});

/**
 * Route for login verify
 *
 */
Router::map('POST', '/dashboard/auth', function () {
    Session::start();

    // WTF! You're logged in already!
    if (AuthModel::isLogged()) {
        return Response::redirect('dashboard', true, 301)->send(1);
    }

    if (AuthModel::verifyForm()) {
        return Response::redirect('dashboard', true, 301)->send(1);
    }

    return Response::redirect('dashboard/login', true, 301)->send();
});

/**
 * Route for Dashboard
 *
 */
Router::map('GET', '/dashboard', function () use ($view) {
    Session::start();
    // You need to login
    if (!AuthModel::isLogged()) {
        return Response::redirect('dashboard/login', true, 301)->send(1);
    }
    Registry::set('page.title', 'Dashboard');
    BreadCrumbs::add('Dashboard', base_url('dashboard'));

    return $view->render('index');
});


/**
 * Route for Site Settings
 *
 */
Router::map('GET', '/dashboard/settings', function () use ($view) {
    Session::start();

    // You need to login
    if (!AuthModel::isLogged()) {
        return Response::redirect('dashboard/login', true, 301)->send(1);
    }
    Registry::set('page.title', 'Site Settings');

    BreadCrumbs::add('Dashboard', base_url('dashboard'))
    ->add('Site Settings', base_url('dashboard/settings'));

    return $view->render('settings');
});

Router::map('GET', '/dashboard/themes', function () use ($view) {
    Session::start();

    // You need to login
    if (!AuthModel::isLogged()) {
        return Response::redirect('dashboard/login', true, 301)->send(1);
    }
    Registry::set('page.title', 'Theme Manager');

    BreadCrumbs::add('Dashboard', base_url('dashboard'))
    ->add('Theme Manager', base_url('dashboard/themes'));

    $themes = OptionModel::getThemeList();
    $view->assign('themes', $themes);

    return $view->render('themes');
});


/**
 * Route for Processing Dashboard Update Request
 *
 * @param string $action The Update Action Name
 */
Router::map('POST', '/dashboard/update/[settings|permalinks|dmca|ads|password|themes:action]', function ($action) {
    Session::start();

    // You need to login
    if (!AuthModel::isLogged()) {
        return Response::redirect('dashboard/login', true, 301)->send(1);
    }

    if (defined('DEMO_MODE') && DEMO_MODE === true) {
        Alert::add('error', 'Thanks! but no settings were updated since the script is in demo mode!');
        return Response::redirect('dashboard' . '/' . $action, true, 301)->send(1);
    }

    if ($action === 'settings') {
        DashModel::updateSettings();
    } elseif ($action === 'permalinks') {
        DashModel::updatePermalinks();
    } elseif ($action === 'dmca') {
        DashModel::updateDmca();
    } elseif ($action === 'password') {
        DashModel::updatePass();
    } elseif ($action === 'themes') {
        DashModel::updateTheme();
    } else {
        DashModel::updateAds();
    }

    return Response::redirect('dashboard' . '/' . $action, true, 301)->send(1);
});


/**
 * Route for Permalinks Management
 *
 */
Router::map('GET', '/dashboard/permalinks', function () use ($view) {
    Session::start();

    // You need to login
    if (!AuthModel::isLogged()) {
        return Response::redirect('dashboard/login', true, 301)->send(1);
    }
    Registry::set('page.title', 'Permalink Settings');

    BreadCrumbs::add('Dashboard', base_url('dashboard'))
    ->add('Permalink Settings', base_url('dashboard/permalinks'));

    return $view->render('permalinks');
});

/**
 * Route for API Keys Management
 *
 * @var integer $page
 */
Router::map('GET', '/dashboard/api-keys[/page/]?[i:page]?', function ($page = 1) use ($view) {
    Session::start();

    // You need to login
    if (!AuthModel::isLogged()) {
        return Response::redirect('dashboard/login', true, 301)->send(1);
    }
    Registry::set('page.title', 'API Keys');

    BreadCrumbs::add('Dashboard', base_url('dashboard'))
    ->add('API Keys', base_url('dashboard/api-keys'));

    $total = ApiModel::getTotalApiKeyCount();
    $items_per_page = 5;
    $pagination = new Pagination($total, $page, $items_per_page);
    $pagination->setLineFormat('<li class="@class@"><a href="' . base_url('dashboard/api-keys/page/@id@') . '">@label@</a></li>', 'active', 'page-item');
    $pages = $pagination->renderHtml();

    $offset = $pagination->offset();

    $api_keys = ApiModel::listApi($offset, $items_per_page);

    $view->assign('pagination', $pages);
    $view->assign('page', $page);
    $view->assign('total', $total);
    $view->assign('offset', $offset);
    $view->assign('items_per_page', $items_per_page);
    $view->assign('api_keys', (array)$api_keys);

    return $view->render('api-keys');
});

/**
 * Route for Processing Add API Request
 *
 */
Router::map('POST', '/dashboard/addApi', function () {
    Session::start();

    // You need to login
    if (!AuthModel::isLogged()) {
        return Response::redirect('dashboard/login', true, 301)->send(1);
    }

    if (defined('DEMO_MODE') && DEMO_MODE === true) {
        Alert::add('error', 'Thanks! but no settings were updated since the script is in demo mode!');
        return Response::redirect('dashboard/api-keys', true, 301)->send(1);
    }

    DashModel::addApi();
    return Response::redirect('dashboard/api-keys', true, 301)->send(1);
});

/**
 * Route for Deleting API
 *
 * @param integer $id API ID
 */
Router::map('GET', '/dashboard/delApi/[i:id]', function ($id) {
    Session::start();

    // You need to login
    if (!AuthModel::isLogged()) {
        return Response::redirect('dashboard/login', true, 301)->send(1);
    }

    if (defined('DEMO_MODE') && DEMO_MODE === true) {
        Alert::add('error', 'Thanks! but no settings were updated since the script is in demo mode!');
        return Response::redirect('dashboard/api-keys', true, 301)->send(1);
    }

    DashModel::deleteApi($id);
    return Response::redirect('dashboard/api-keys', true, 301)->send(1);
});

/**
 * Route for DMCA Management
 *
 */
Router::map('GET', '/dashboard/dmca', function () use ($view) {
    Session::start();

    // You need to login
    if (!AuthModel::isLogged()) {
        return Response::redirect('dashboard/login', true, 301)->send(1);
    }

    Registry::set('page.title', 'DMCA Manager');

    BreadCrumbs::add('Dashboard', base_url('dashboard'))
    ->add('DMCA Manager', base_url('dashboard/dmca'));
    return $view->render('dmca');
});

/**
 * Route for Ads Management
 *
 */
Router::map('GET', '/dashboard/ads', function () use ($view) {
    Session::start();

    // You need to login
    if (!AuthModel::isLogged()) {
        return Response::redirect('dashboard/login', true, 301)->send(1);
    }

    Registry::set('page.title', 'Ads Manager');

    BreadCrumbs::add('Dashboard', base_url('dashboard'))
    ->add('Ads Manager', base_url('dashboard/ads'));
    return $view->render('ads');
});

/**
 * Route for Change Password Page
 *
 */
Router::map('GET', '/dashboard/password', function () use ($view) {
    Session::start();

    // You need to login
    if (!AuthModel::isLogged()) {
        return Response::redirect('dashboard/login', true, 301)->send(1);
    }


    Registry::set('page.title', 'Change Password');

    BreadCrumbs::add('Dashboard', base_url('dashboard'))
    ->add('Change Password', base_url('dashboard/password'));

    return $view->render('password');
});


/**
 * Route for processing logout request
 *
 */
Router::map('GET', '/dashboard/logout', function () {
    Session::start();
    if (AuthModel::logOut()) {
        return Response::redirect('dashboard/login', true, 301)->send();
    }

    return Response::redirect('/', true, 301)->send();
});
