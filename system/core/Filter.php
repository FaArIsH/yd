<?php
namespace Oishy\Core;

use RuntimeException;

/**
* A basic string escape and filtering class
*
* @version 1.0
* @since 0.1
*/
class Filter
{
    public static $charset = 'UTF-8';

    /**
     * Applies htmlspecialchars() with ENT_QUOTES and UTF-8 as default
     *
     * @param  string|array|object $input    The input to escape.
     * @param  boolean $double_encode Toggle double encode, defaults to TRUE
     * @return string
     */
    public static function htmlEscape($input, $double_encode = true)
    {
        if (is_array($input)) {
            foreach (array_keys($input) as $key) {
                $input[$key] = self::spchars($input[$key], $double_encode);
            }

            return $input;
        }

        return htmlspecialchars($input, ENT_QUOTES, self::$charset, $double_encode);
    }

    /**
     * Applies htmlentities() with ENT_QUOTES | ENT_HTML5 and UTF-8 as default
     *
     * @param  string|array|object $input    The input to escape
     * @param  boolean $double_encode Toggle double encode, defaults to TRUE
     * @return string
     */
    public static function htmlEntities($input, $double_encode = true)
    {
        if (is_array($input)) {
            foreach (array_keys($input) as $key) {
                $input[$key] = self::htmlEntities($input[$key], $double_encode);
            }

            return $input;
        }

        return htmlentities($input, ENT_QUOTES | ENT_HTML5, self::$charset, $double_encode);
    }

    /**
     * Apply multiple filters to variable
     *
     * @param mixed $var       The variable
     * @param string $functions Pipe separated list of custom functions to apply
     *                          ex. strip_tags|stroupper|trim.
     * @return mixed
     */
    public static function batch($var, $functions = null)
    {
        if (!$functions) {
            return $var;
        }

        foreach (explode('|', $functions) as $func) {
            if (is_callable($func)) {
                $var = call_user_func($func, $var);
            } else {
                throw new RuntimeException('Unable to call function '.$func);
            }
        }
        return $var;
    }
}
