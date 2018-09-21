<?php
namespace Oishy\Core;

/**
* Manages alerts aka feedback messages.
*
* @version 1.0
* @since 0.1
*/
class Alert
{
    /**
     * Alerts types
     *
     * @var array
     */
    public static $alert_types = ['error', 'notice', 'success', 'warning'];

    /**
     * Session key prefix to store alerts
     *
     * @var string
     */
    public static $alert_session_prefix = '__oishy_alerts.';

    /**
     * Add alert message(s)
     *
     *
     * @param string $type     The alert type. 'error', 'notice', 'success', 'warning'
     * @param array|string $messages The messages as array. If it is a single one,
     *                         you can input it as a string.
     * @return boolean
     */
    public static function add($type, $messages)
    {
        if (!self::isValidType($type)) {
            return false;
        }

        // If it is a single message, convert it to an array
        if (!is_array($messages)) {
            $messages = [$messages];
        }

        $key = sprintf(self::$alert_session_prefix . '%s', $type);
        $messages = array_merge(Session::get($key, []), $messages);
        Session::set($key, $messages);
        return true;
    }

    /**
     * Read alert messages of a specific type, this will also delete the messages
     * from session to avoid duplicates
     *
     * @param string $type     The alert type. 'error', 'notice', 'success', 'warning'
     * @return array
     */
    public static function read($type)
    {
        if (!self::isValidType($type)) {
            return false;
        }

        $key = sprintf(self::$alert_session_prefix . '%s', $type);
        $messages = Session::get($key, []);
        Session::remove($key);
        return $messages;
    }

    /**
     * Display alert messages of a specific type, this will also delete the messages
     * from session to avoid duplicates
     *
     * @param string $type   The alert type. 'error', 'notice', 'success', 'warning'
     * @param string $before HTML/Text to display before the messages.
     *                       Note: %type% will be replaced with alert type
     * @param string $after HTML/Text to display after the messages.
     *                       Note: %type% will be replaced with alert type
     * @return
     */
    public static function show(
        $type,
        $before = '<div class="toast toast-%type%">',
        $after = '</div>'
    ) {
        $alerts = self::read($type);
        // Why should i even continue?
        if (empty($alerts)) {
            return '';
        }
        $before = str_ireplace('%type%', $type, $before);
        $after = str_ireplace('%type%', $type, $after);
        echo $before;
        echo implode('<br>', $alerts);
        echo $after;
    }

    /**
     * Add alert type
     *
     * @param string $type The alert type
     */
    public static function addType($type)
    {
        if (!is_string($type)) {
            return false;
        }
        self::$alert_types[] = trim($type);
        return true;
    }

    /**
     * Check if the provided alert type is valid
     *
     * @access protected
     * @param string $type The alert type. 'error', 'notice', 'success', 'warning'
     * @return boolean
     */
    protected static function isValidType($type)
    {
        $type = mb_strtolower($type);

        if (!in_array($type, self::$alert_types)) {
            return false;
        }
        return true;
    }

    public static function __callStatic($method, $paramaters = [])
    {
        self::add($method, array_shift($paramaters));
    }
}
