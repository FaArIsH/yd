<?php

/**
 * Application Environment
 *
 * Accepted values: production/prod/testing and development/dev
 */
define('ENVIRONMENT', 'dev');

/**
 * The framework version
 */
define('OISHY_VERSION', '0.1');

/**
 * The direcctory separator to be used across the app, defaults to system
 */
define('DS', '/');

/**
 * Absolute Path to the framework root, with directory separator at end
 */
define('ROOT', __DIR__ . DS);

/**
 * Absolute Path to the System Directory, with directory separator at end
 */
define('SYSTEM', ROOT . 'system' . DS);

/**
 * Absolute Path to the Application Directory, with directory separator at end
 *
 * Warning: if you make change to this,
 * make sure to also change the path to vendor directory in composer.json
 */
define('APP', ROOT . 'app' . DS);

/**
 * Enabling Demo mode will disable updating/changing settings in admin panel
 *
 */
define('DEMO_MODE', false);
