<?php

/**
 * Oishy - The Divine Atomic PHP Framework!
 *
 * @author Miraz Mac <mirazmac@gmail.com>
 * @version 1.0
 * @since 0.1
 */

// Make sure the PHP version is at-least 5.4
if (!version_compare(PHP_VERSION, '5.4', '>=')) {
    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo 'You need at-least PHP 5.4 to run Oishy. Please upgrade your PHP.
    Your current PHP version is '. PHP_VERSION;
    exit(1);
}

// Set the error reporting according to the environment
switch (@strtolower(ENVIRONMENT)) {
    case 'development':
    case 'dev':
        error_reporting(-1);
        ini_set('display_errors', 1);
        break;

    case 'testing':
    case 'production':
    case 'prod':
        ini_set('display_errors', 0);
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        break;

    default:
        header('HTTP/1.1 503 Service Unavailable.', true, 503);
        echo 'The application environment is not set correctly.<br/>
        Please define ENVIRONMENT constant in index.php';
        exit(1);
}

// Load core functions
require_once SYSTEM . 'core/Functions.php';

// Load and intialize Oishy's Core Autoloader
require_once syspath('core/Autoloader.php');

use Oishy\Core\Autoloader;

Autoloader::addNamespace('Oishy\Core', syspath('core'));
Autoloader::register();
// Import classess
use Oishy\Core\Config;
use Oishy\Core\Router;
use Oishy\Driver\Registry;

// Register custom namespaces for autoloading
foreach (Config::get('autoload.aliases', []) as $namespace => $directory) {
    Autoloader::addNamespace($namespace, $directory);
}

// Register custom directories for autoloading of non-namespaced classes
foreach (Config::get('autoload.directories', []) as $dir_name) {
    Autoloader::addDirectory($dir_name);
}

unset($directory, $namespace, $dir_name);

// Register custom error and exception handler
Oishy\Core\ExceptionHandler::start(syspath('static/html/exception.html'));

/**
 * Load composer dependencies if available
 *
 * !! Important: Don't forget to run "composer install" !!
 */
if (file_exists(apppath('vendor/autoload.php'))) {
    require apppath('vendor/autoload.php');
}

// Autoload custom helpers
foreach ((array)Config::get('autoload.helpers') as $helper) {
    require_once apppath('helpers/' . $helper . '');
}


$charset = mb_strtoupper(Config::get('app.charset', 'UTF-8'));

// Set Internal charset
ini_set('default_charset', $charset);
mb_internal_encoding($charset);
mb_http_output($charset);

// Unset the variables to avoid future re-use or conflicts
unset($charset);

// Set server timezone
if (Config::get('app.timezone', false)) {
    date_default_timezone_set(Config::get('app.timezone'));
}

// Change the session name
if (Config::get('cookie.session_cookie_name', false)) {
    session_name(Config::get('cookie.session_cookie_name'));
}

// Change the session save path
if (Config::get('app.session_save_path', false)) {
    session_save_path(Config::get('app.session_save_path'));
}

// Change session cookie parameters
session_set_cookie_params(
    (int)Config::get('cookie.session_lifetime'),
    Config::get('cookie.path', '/'),
    Config::get('cookie.domain'),
    (bool)Config::get('cookie.secure'),
    (bool)Config::get('cookie.httponly')
);

/**
 * Secure headers
 *
 * Bring my violin Watson!
 */

// Enable strict transport security, if the connection is SSL
if (is_https()) {
    header('Strict-Transport-Security: max-age=16070400; includeSubDomains');
}

// Disable framing, except same site
header('X-Frame-Options: sameorigin');

// Enable Browser level XSS protection
header('X-XSS-Protection: 1; mode=block');

// Disable sniffing
header('X-Content-Type-Options: nosniff');

// Remove PHP version from header
header_remove('x-powered-by');

if (!is_dir(Config::get('route.directory'))) {
    throw new \Exception("Failed to read route directory.");
}

foreach (Config::get('route.files', []) as $route_file) {
    require dirslashit(Config::get('route.directory', '')) . $route_file;
}

$match = Router::match(oishy_route_path());

if ($match && is_callable($match['target'])) {
    // For advanced usage we would store the route parameters into registry
    // With read only flag, so other codes can use them inside their scope
    Registry::set('route.params', $match['params'], true);
    call_user_func_array($match['target'], $match['params']);
} else {
    // we need to fire the 404 callback
    trigger_404_error();
}
