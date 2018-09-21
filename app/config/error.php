<?php
/**
 * Error Configuration
 *
 * @since 0.1
 */

return [

    /**
     * Whether to report errors or not.
     *
     * Error reporting is controlled via ENVIRONMENT setting. But this
     * configuration allows you to control the visibility of errors.
     * If you disable visibility of errors here, errors wont be displayed,
     * but it won't affect the "error_reporting" setting.
     *
     * @var boolean
     */
    'report' => true,

    /**
     * Error logger callback
     *
     * @var callback
     */
    'logger' => function ($e) {
        $data = sprintf(
            "Environment: %s\nDate: %s\nMessage: %s\nSource: %s, at line %s\n\n",
            ENVIRONMENT,
            date("Y-m-d H:i:s A"),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        $write = file_put_contents(APP . 'errors.log', $data, FILE_APPEND | LOCK_EX);
    },

    /**
     * Callback to fire when a 404 error occures
     *
     * @var callback
     */
    '404_callback' => function ($args = []){
        echo "Dang! Error 404!";
    },

    ];
