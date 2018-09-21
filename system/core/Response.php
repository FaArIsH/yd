<?php
namespace Oishy\Core;

/**
* Http Response Class
*
* Basically for sending headers and creating redirects
* @since 0.1
* @version 1.0
*/

class Response
{
    /**
     * Http Codes and their response messages
     *
     * @var array
     */
    public static $messages = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded'
    ];

    /**
     * Http status code to send
     *
     * @var integer
     */
    public static $status = 200;

    /**
     * Http headers as array
     *
     * @var array
     */
    public static $headers = [];

    /**
     * Content to output after sending headers
     *
     * @var mixed
     */
    public static $output = null;

    /**
     * Create a Response instance
     *
     * @param integer $status  HTTP status code
     * @param array   $headers HTTP headers as key => value format
     * @param mixed   $output  Content to output after sending headers
     * @return object instance to allow chaining
     */
    public static function create($status = 200, array $headers = [], $output = null)
    {
        self::status($status);
        self::headers($headers);
        self::output($output);

        return new static();
    }

    /**
     * Set http status code
     *
     * @param  integer $status_code HTTP status code
     * @return object instance to allow chaining
     */
    public static function status($status_code = 200)
    {
        self::$status = $status_code;
        return new static();
    }

    /**
     * Set HTTP headers
     *
     * @param  array  $headers HTTP headers as key => value forma
     * @return object instance to allow chaining
     */
    public static function headers(array $headers = [])
    {
        foreach ($headers as $name => $value) {
            self::$headers[$name] = $value;
        }
        return new static();
    }

    /**
     * Set output content
     *
     * @param  mixed $content Content to output after sending headers
     * @return object instance to allow chaining
     */
    public static function output($content)
    {
        self::$output = $content;
        return new static();
    }

    /**
     * Output JSON data
     *
     * @param  mixed  $output  The data you want to run through json_encode()
     * @param  integer $status  HTTP status code
     * @param  string  $charset Output character set. Defaults to Config::get('app.charset')
     * @return object instance to allow chaining
     */
    public static function json($output, $status = 200, $charset = null)
    {
        if (!$charset) {
            $charset = Config::get('app.charset');
        }
        return self::create($status,
            [
            'Content-Type' => 'application/json; charset=' . $charset
            ], json_encode($output));
    }

    /**
     * Create a redirect
     *
     * @param  string  $target Target URL/path
     * @param  boolean $native Whether to append native URL before the path
     *                         Defaults to TRUE
     * @param  integer $status Http status code
     * @return Response
     */
    public static function redirect($target, $native = true, $status = 301)
    {
        $url = '';
        if ($native) {
            $url .= Config::get('app.url', oishy_detect_url());
        }
        $url .= ltrim($target, '/');

        // Create a new instance
        return self::create($status, ['Location' => $url]);
    }

    /**
     * Send the response
     *
     * @param boolean $exit Whether to stop the script or not after sending the response
     * @return
     */
    public static function send($exit = false)
    {
        $protocol = Request::server('SERVER_PROTOCOL', 'HTTP/1.1');
        $status = self::$status;
        if (!isset(self::$messages[$status])) {
            throw new \InvalidArgumentException("Invalid HTTP Response Code '{$status}'");
        }
        $message = self::$messages[$status];

        // Flush buffer in case headers are sent and output_buffering is on
        while (ob_get_level() > 1) {
            ob_end_clean();
        }

        // For FastCGI we need to send the response header specially
        if (strpos(PHP_SAPI, 'cgi')) {
            header('Status: ' . $status . ' ' . $message);
        } else {
            header($protocol . ' ' . $status .  ' ' . $message);
        }
        // Yeah, just send the headers -_-
        foreach (self::$headers as $name => $value) {
            header($name . ': ' . $value);
        }

        // You got something?
        if (self::$output) {
            echo self::$output;
        }

        // To exit or not to exit that is not a question, really -_-
        if ($exit) {
            exit(1);
        }
    }
}
