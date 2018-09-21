<?php

/**
 * Core Functions
 *
 * IMPORTANT: The functions here are the part of Oishy's core. They are used by
 * Core classes of Oishy, so you better not touch them.
 *
 * @version 1.0
 * @since 0.1
 */

//=========================================================
// Core oishy functions
//=========================================================

if (!function_exists('oishy_detect_url')) {
    /**
     * Detects site URL
     *
     * @return string
     */
    function oishy_detect_url()
    {
        $protocol = is_https() ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $script = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        return $protocol . $host . $script;
    }
}


if (!function_exists('base_url')) {
    /**
     * Quick access the Site URL
     *
     * @param string $path (Optional) The path
     * @return string
     */
    function base_url($path = '')
    {
        $uri = \Oishy\Core\Config::get('app.url', oishy_detect_url());
        // Remove begining trailing slash if exists
        $path = ltrim($path, '/');
        $uri = rtrim($uri, '/');
        $uri .= '/' . $path;
        return $uri;
    }
}

if (!function_exists('load_helper')) {

    /**
     * Load user's custom defined functions aka helpers
     *
     * @param string|array $helper_file_names The helper file name
     * @return integer Number of files loaded
     */
    function load_helper($helper_file_names)
    {
        if (!is_array($helper_file_names)) {
            $helper_file_names = [$helper_file_names];
        }
        $i = 0;
        foreach ($helper_file_names as $helper_file_name) {
            require apppath('helpers/' . $helper_file_name, false);
            $i++;
        }
        return $i;
    }
}

if (!function_exists('get_random_string')) {

    /**
     * Get a random string. Not secure! Just a random string!
     *
     * @param integer $length The length of the string
     * @param string  $keyspace  The characters to use, Default is: 0-9-A-z
     * @return string
     */
    function get_random_string($length = 10, $keyspace =
        'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTWXYZ0123456789')
    {
        $length = (int)$length;
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            if (function_exists('random_int')) {
                $k = random_int(0, $max);
            } elseif (function_exists('mt_rand')) {
                $k = mt_rand(0, $max);
            } else {
                $k = rand(0, $max);
            }

            $str .= $keyspace[$k];
        }
        return $str;
    }
}

if (!function_exists('get_secure_token')) {
    /**
     * Generate a cryptographically secure random token
     *
     * @param integer $length The length of token
     *                        ( may differ because it will be converted to hex )
     * @return string
     */
    function get_secure_token($length = 20)
    {
        // for PHP7 we have another great solution
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length));
        }
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}


if (!function_exists('get_current_url')) {
    /**
     * Get current URL
     *
     * @return string
     */
    function get_current_url()
    {
        $page_url = '';

        if (is_https()) {
            $page_url .= 'https://';
        } else {
            $page_url .= 'http://';
        }
        if ($_SERVER['SERVER_PORT'] != '80') {
            $page_url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        } else {
            $page_url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        }
        return $page_url;
    }
}


if (!function_exists('oishy_url_slug')) {
    /**
     * Generate SEO friendly URL slug from string. With Unicode support.
     *
     * @param string $str     The string
     * @param array  $options The options as array, the option keys are:
     *                        delimiter : URL delimiter, default is "-"
     *                        limit : how long the slug could be? default is "null"
     *                        lowercase : whether to convert it to lowercase or not
     *                        replacements : custom replacements, as key => val array
     *                        transliterate : Transliterate characters to ASCII or not
     *
     * @return string
     */
    function oishy_url_slug($str, array $options = [])
    {
        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

        $defaults = [
            'delimiter' => '-',
            'limit' => null,
            'lowercase' => true,
            'replacements' => [],
            'transliterate' => false,
            ];

        // Merge options
        $options = array_merge($defaults, $options);

        $char_map = [
        // Latin
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'O' => 'O',
            'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'U' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
            'ß' => 'ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'o' => 'o',
            'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'u' => 'u', 'ý' => 'y', 'þ' => 'th',
            'ÿ' => 'y',
        // Latin symbols
            '©' => '(c)',
        // Greek
            '?' => 'A', '?' => 'B', 'G' => 'G', '?' => 'D', '?' => 'E', '?' => 'Z', '?' => 'H', 'T' => '8',
            '?' => 'I', '?' => 'K', '?' => 'L', '?' => 'M', '?' => 'N', '?' => '3', '?' => 'O', '?' => 'P',
            '?' => 'R', 'S' => 'S', '?' => 'T', '?' => 'Y', 'F' => 'F', '?' => 'X', '?' => 'PS', 'O' => 'W',
            '?' => 'A', '?' => 'E', '?' => 'I', '?' => 'O', '?' => 'Y', '?' => 'H', '?' => 'W', '?' => 'I',
            '?' => 'Y',
            'a' => 'a', 'ß' => 'b', '?' => 'g', 'd' => 'd', 'e' => 'e', '?' => 'z', '?' => 'h', '?' => '8',
            '?' => 'i', '?' => 'k', '?' => 'l', 'µ' => 'm', '?' => 'n', '?' => '3', '?' => 'o', 'p' => 'p',
            '?' => 'r', 's' => 's', 't' => 't', '?' => 'y', 'f' => 'f', '?' => 'x', '?' => 'ps', '?' => 'w',
            '?' => 'a', '?' => 'e', '?' => 'i', '?' => 'o', '?' => 'y', '?' => 'h', '?' => 'w', '?' => 's',
            '?' => 'i', '?' => 'y', '?' => 'y', '?' => 'i',
        // Turkish
            'S' => 'S', 'I' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'G' => 'G',
            's' => 's', 'i' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'g' => 'g',
        // Russian
            '?' => 'A', '?' => 'B', '?' => 'V', '?' => 'G', '?' => 'D', '?' => 'E', '?' => 'Yo', '?' => 'Zh',
            '?' => 'Z', '?' => 'I', '?' => 'J', '?' => 'K', '?' => 'L', '?' => 'M', '?' => 'N', '?' => 'O',
            '?' => 'P', '?' => 'R', '?' => 'S', '?' => 'T', '?' => 'U', '?' => 'F', '?' => 'H', '?' => 'C',
            '?' => 'Ch', '?' => 'Sh', '?' => 'Sh', '?' => '', '?' => 'Y', '?' => '', '?' => 'E', '?' => 'Yu',
            '?' => 'Ya',
            '?' => 'a', '?' => 'b', '?' => 'v', '?' => 'g', '?' => 'd', '?' => 'e', '?' => 'yo', '?' => 'zh',
            '?' => 'z', '?' => 'i', '?' => 'j', '?' => 'k', '?' => 'l', '?' => 'm', '?' => 'n', '?' => 'o',
            '?' => 'p', '?' => 'r', '?' => 's', '?' => 't', '?' => 'u', '?' => 'f', '?' => 'h', '?' => 'c',
            '?' => 'ch', '?' => 'sh', '?' => 'sh', '?' => '', '?' => 'y', '?' => '', '?' => 'e', '?' => 'yu',
            '?' => 'ya',
        // Ukrainian
            '?' => 'Ye', '?' => 'I', '?' => 'Yi', '?' => 'G',
            '?' => 'ye', '?' => 'i', '?' => 'yi', '?' => 'g',
        // Czech
            'C' => 'C', 'D' => 'D', 'E' => 'E', 'N' => 'N', 'R' => 'R', 'Š' => 'S', 'T' => 'T', 'U' => 'U',
            'Ž' => 'Z',
            'c' => 'c', 'd' => 'd', 'e' => 'e', 'n' => 'n', 'r' => 'r', 'š' => 's', 't' => 't', 'u' => 'u',
            'ž' => 'z',
        // Polish
            'A' => 'A', 'C' => 'C', 'E' => 'e', 'L' => 'L', 'N' => 'N', 'Ó' => 'o', 'S' => 'S', 'Z' => 'Z',
            'Z' => 'Z',
            'a' => 'a', 'c' => 'c', 'e' => 'e', 'l' => 'l', 'n' => 'n', 'ó' => 'o', 's' => 's', 'z' => 'z',
            'z' => 'z',
        // Latvian
            'A' => 'A', 'C' => 'C', 'E' => 'E', 'G' => 'G', 'I' => 'i', 'K' => 'k', 'L' => 'L', 'N' => 'N',
            'Š' => 'S', 'U' => 'u', 'Ž' => 'Z',
            'a' => 'a', 'c' => 'c', 'e' => 'e', 'g' => 'g', 'i' => 'i', 'k' => 'k', 'l' => 'l', 'n' => 'n',
            'š' => 's', 'u' => 'u', 'ž' => 'z'
            ];
        //Make custom replacements
        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);
        // Transliterate characters to ASCII
        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }
        // Replace non-alphanumeric characters with our delimiter
        // Little modded by @mirazmac to support the full language structure
        $str = preg_replace("/[^\p{L}\p{Nd}\p{M}]+/u", $options['delimiter'], $str);

        // Remove duplicate delimiters
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

        // Truncate slug to max. characters
        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

        // Remove delimiter from ends
        $str = trim($str, $options['delimiter']);
        return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }
}

if (!function_exists('is_https')) {
    /**
     * Is HTTPS?
     *
     * Determines if the application is accessed via an encrypted
     * (HTTPS) connection.
     *
     * @return  boolean
     */
    function is_https()
    {
        if (!empty($_SERVER['HTTPS']) && mb_strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && mb_strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            return true;
        } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && mb_strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        }

        return false;
    }
}

if (!function_exists('is_php')) {
    /**
     * Determines if the current version of PHP is equal to or greater than the supplied value
     *
     * @param   string
     * @return  bool    TRUE if the current version is $version or higher
     */
    function is_php($version)
    {
        static $_is_php;
        $version = (string)$version;

        if (!isset($_is_php[$version])) {
            $_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
        }

        return $_is_php[$version];
    }
}

if (!function_exists('is_dev')) {
    /**
     * If current ENVIRONMENT is set to development or not
     *
     * @return boolean
     */
    function is_dev()
    {
        return ENVIRONMENT === 'dev' || ENVIRONMENT === 'development';
    }
}

if (!function_exists('oishy_route_path')) {
    /**
     * Get route formatted path from REQUEST_URI
     *
     * @return string
     */
    function oishy_route_path()
    {
        $uri = parse_url(get_current_url(), PHP_URL_PATH);
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : false;
        if (!$request_uri) {
            $request_uri = $_SERVER['PHP_SELF'];
        }
        $script_name = $_SERVER['SCRIPT_NAME'];
        $base_uri = '';

        if (mb_strpos($request_uri, $script_name) === 0) {
            $base_uri .= $script_name;
        } else {
            $base_uri .= str_replace('\\', '/', dirname($script_name));
        }
        $base_uri = rtrim($base_uri, '/');

        if ($base_uri !== '' && mb_strpos($uri, $base_uri) === 0) {
            $uri = mb_substr($uri, mb_strlen($base_uri));
        }
        $uri = rawurldecode($uri);

        return (string)'/' . ltrim($uri, '/');
    }
}


if (!function_exists('basepath')) {
    /**
     * Get absolute path string
     *
     * @param  string  $path The path
     * @param  boolean $ds   Toggle appending of directory separator at end ( FALSE by default)
     * @param  string|boolean  $base Custom Base path, ROOT is used as default
     * @return string
     */
    function basepath($path = '/', $ds = false, $base = null)
    {
        // If base is not defined use ROOT as base path
        if (!$base) {
            $base = ROOT;
        }

        // Convert directory separators to OS specific ones
        $path = str_replace(['\\', '/'], DS, $path);
        $app_path = dirslashit($base) . $path;

        // Append directory separator at end on-demand
        if ($ds) {
            $app_path = dirslashit($app_path);
        }
        return (string)$app_path;
    }
}

if (!function_exists('apppath')) {
    /**
     * Alias of basepath() uses APP as base directory
     *
     * @param  string  $path The path
     * @param  boolean $ds   Toggle appending of directory separator at end ( FALSE by default)
     * @return string
     */
    function apppath($path = '/', $ds = false)
    {
        return basepath($path, $ds, APP);
    }
}

if (!function_exists('syspath')) {
    /**
     * Alias of basepath() uses SYSTEM as base directory
     *
     * @param  string  $path The path
     * @param  boolean $ds   Toggle appending of directory separator at end ( FALSE by default)
     * @return string
     */
    function syspath($path = '/', $ds = false)
    {
        return basepath($path, $ds, SYSTEM);
    }
}

if (!function_exists('tralingslashit')) {
    /**
     * Add directory separator to end of a string
     *
     * @param  string $string The string
     * @param  string $slash     The trailing slash to add, defaults to: '/'
     * @return string
     */
    function trailingslashit($string, $slash = '/')
    {
        return untrailingslashit($string) . $slash;
    }
}

if (!function_exists('untrailingslashit')) {
    /**
     * Remove trailing slashes
     *
     * @param  string $string The string
     * @return string
     */
    function untrailingslashit($string)
    {
        return rtrim($string, '/\\');
    }
}

if (!function_exists('dirslashit')) {
    /**
     * Add directory separator to end of a string
     *
     * @param  string $string The string
     * @param  string $ds     The directory separator to add, defaults to DS
     * @return string
     */
    function dirslashit($string, $ds = DS)
    {
        return trailingslashit($string, $ds);
    }
}

if (!function_exists('undirslashit')) {
    /**
     * Remove trailing directory separators
     *
     * @param  string $string The string
     * @return string
     */
    function undirslashit($string)
    {
        return untrailingslashit($string);
    }
}

/**
 * Provides Simple String Templating for PHP
 *
 * @param  string $string The string
 * @param  string|array $params The Parameters as query string or array
 * @author Miraz Mac <mirazmac@gmail.com>
 * @return string
 */
function strtpl($string, $params)
{
    // Racism alert! String only!
    if (!is_string($string)) {
        trigger_error("_sprintf() expects parameter \$string to be string, " . gettype($string) . " given!", E_USER_WARNING);
    }
    // See if we need parsing
    if (!is_array($params)) {
        parse_str($params, $params);
    }
    $string = preg_replace_callback('/%(\s*?[a-zA-Z0-9_-]*\s*?)%/', function ($match) use ($params) {
        // Grab the token, and remove all leading and trailing spaces
        $token = trim($match[1]);
        // Make sure we ignore blank tokens
        if (empty($token)) {
            return $match[0];
        }
        // Show an warning in-case the parameter is not provided!
        if (!array_key_exists($token, $params)) {
            //trigger_error("No value for parameter { {$token} } is provided!", E_USER_WARNING);
            return $match[0];
        }
        return $params[$token];
    }, $string);
    return $string;
}

function trigger_404_error()
{
    Oishy\Core\Response::status(404)->send();
    call_user_func(Oishy\Core\Config::get('error.404_callback'));
}

function base64_url_encode($input)
{
    return strtr(base64_encode($input), '+/=', '-_,');
}
function base64_url_decode($input)
{
    return base64_decode(strtr($input, '-_,', '+/='));
}
