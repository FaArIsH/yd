<?php
/**
 * Router Configuration
 *
 * @since 0.1
 */

return [

    /**
     * Base directory for router files
     *
     * @var string
     */
    'directory' => apppath('routes'),

    /**
     * Array of route file names, as located in route.directory
     *
     * @var array
     */
    'files' => [
        'base.php', 'dashboard.php', 'videos.php',
        ]
    ];
