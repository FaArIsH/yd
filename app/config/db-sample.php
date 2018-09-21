<?php
/**
 * Database Configuration
 *
 * @since 0.1
 */

return [

    'dsn' => '%dsn%',

    /**
     * Database username
     *
     * @var string
     */
    'username' => '%username%',

    /**
     * Database user password
     *
     * @var string
     */
    'password' => '%password%',

    /**
     * Set PDO Fetch mode
     *
     * @var  mixed
     */
    'fetch_mode' => \PDO::FETCH_ASSOC,

    /**
     * Set PDO Error Mode
     *
     * @var mixed
     */
    'err_mode' => \PDO::ERRMODE_EXCEPTION
    ];
