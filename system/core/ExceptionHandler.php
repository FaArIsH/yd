<?php
namespace Oishy\Core;

/**
 * A simple exception handler.
 *
 * @author  Fabien Nouaillat
 * @version 1.0
 * @since 0.1
 */
class ExceptionHandler
{
    protected static $file_path;
    protected static $file;
    protected static $line;
    protected static $message;
    protected static $template;
    protected static $type;
    protected static $trace;

    /**
     * Turns on exception handling.
     *
     * @param  $template The path of the template file
     *
     * @throws InvalidArgumentException if the file cannot be found
     */
    public static function start($template)
    {
        // Checks the file
        if (!is_file($template)) :
            throw new \InvalidArgumentException(sprintf(
                'The file %s cannot be found.', $template
            ));
        endif;
        // Launches the output buffer
        ob_start();
        self::$template = $template;
        set_exception_handler([__CLASS__ , 'register']);
        set_error_handler([__CLASS__ , 'native']);
        register_shutdown_function([__CLASS__ , 'shutdown']);
    }
    /**
     * Turns off exception handling and restores the previous configuration.
     *
     */
    public static function stop()
    {
        // Shuts down the output buffer
        ob_end_flush();
        restore_exception_handler();
    }

    /**
     * Assignes the exception data to the object's properties
     *
     * @param Object $exception
     */
    public static function register($exception)
    {
        self::$file_path = $exception->getFile();
        self::$file = basename($exception->getFile());
        self::$line = $exception->getLine();
        self::$message = $exception->getMessage();
        self::$type = get_class($exception);
        self::$trace = $exception->getTraceAsString();

        if (Config::get('error.report', true)) {
            self::display();
        }
        call_user_func(Config::get('error.logger'), $exception);
    }

    public static function native($code, $message, $file, $line, $context)
    {
        if ($code & error_reporting()) {
            self::register(new \ErrorException($message, $code, 0, $file, $line));
        }
    }

    public static function shutdown()
    {
        if ($error = error_get_last()) {
            extract($error);
            self::register(new \ErrorException($message, $type, 0, $file, $line));
        }
        return true;
    }

    /**
     * Calls the template, parse it and display it.
     *
     */
    protected static function display()
    {
        $stream     = file_get_contents(self::$template);
        $properties = get_class_vars(__CLASS__);
        // Replaces each property by its value
        foreach ($properties as $key => $value) :
            $variable = sprintf('{{ %s }}', $key);
            $stream = str_replace($variable, $value, $stream);
        endforeach;

        while (ob_get_level() > 1) {
            ob_end_flush();
        }

        // Displays the template
        echo $stream;
    }
}
