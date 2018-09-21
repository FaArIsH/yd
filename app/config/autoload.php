<?php
/**
 * Autoloader Configuration
 *
 * @since 0.1
 */

return [

    /**
     * Array of Helper files to autoload
     * Helpers are located in app/helpers
     *
     * @var array
     */
    'helpers' => ['core_helper.php', 'template_helper.php', 'boot_helper.php'],

    /**
     * Array of Namespace aliases
     * as Namespace => Directory format
     *
     * @var array
     */
    'aliases' => [
        'Oishy\Model' => apppath('models'),
        'Oishy\Driver' => apppath('drivers'),
        'MirazMac\YouFetch' => apppath('drivers/YouFetch'),
        ],

    /**
     * Array of directories to load non-namespaced classes
     * But it's HIGHLY RECOMMENDED to use namespaces for classes to prevent collisions
     *
     * @var array
     */
    'directories' => []
    ];
