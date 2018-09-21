<?php

namespace Oishy\Driver;

/**
* Flash - The Pure PHP Template Engine
*
* @author Miraz Mac <mirazmac@gmail.com>
*/
class Flash
{
    /**
     * The base templates directory
     *
     * @var string
     */
    protected $templates_dir;

    /**
     * Templates file extension without period ( . )
     *
     * @var string
     */
    protected $templates_ext;

    /**
     * Assigned data to templates
     *
     * @var array
     */
    protected $templates_data = [];

    /**
     * Currently rendering template path
     *
     * @var string
     */
    protected $now_rendering;

    /**
     * Current started section name
     *
     * @var string
     */
    protected $started_section = null;

    /**
     * Recorded sections
     *
     * @var array
     */
    protected $sections = [];

    /**
     * Create a new template instance
     *
     * @param string $templates_dir Path to template directory
     * @param string $templates_ext Template file extension ( optional )
     */
    public function __construct($templates_dir, $templates_ext = 'phtml')
    {
        // Make sure we have a readable and existent directory
        if (!is_dir($templates_dir) || !is_readable($templates_dir)) {
            throw new \LogicException("No readable directory found at {$templates_dir}");
        }
        // Set templates directory
        // Normalize windows directory separators
        $templates_dir = str_replace('\\', '/', $templates_dir);
        $this->templates_dir = rtrim($templates_dir, '/\\');
        // Set template extension
        $this->setExt($templates_ext);
    }

    /**
     * Assign variable to template
     *
     * @param  string $name  Valid variable name
     * @param  mixed $value Variable value
     * @return object
     */
    public function assign($name, $value = '')
    {
        $this->templates_data[$name] = $value;
        return $this;
    }

    /**
     * Assign multiple variables
     *
     * @param  array  $data The variables as key => value format
     * @return object
     */
    public function assignMulti(array $data)
    {
        foreach ($data as $var => $value) {
            $this->assign($var, $value);
        }
        return $this;
    }

    /**
     * Set template extension
     *
     * @param string $ext The extension without period
     * @return object
     */
    public function setExt($ext)
    {
        $this->templates_ext = ltrim($ext, '.');
        return $this;
    }

    /**
     * Render a template
     *
     * @param  string|array  $tpl_name    Template name as string or in array
     *                                    for multiple fallback
     * @param  array   $tpl_data    Template data
     * @param  boolean $echo_output Whether to return the parsed template or
     *                              directly echo it out. Defaults to TRUE
     * @throws \InvalidArgumentException If $tpl_name is not of type array or string
     * @throws \LogicException If the specified template not found
     * @return mixed
     */
    public function render($tpl_name, array $tpl_data = [], $echo_output = true)
    {
        // Merge the template data
        $this->templates_data = array_merge($this->templates_data, $tpl_data);

        if (is_string($tpl_name)) {
            $this->now_rendering = $this->buildTplFilePath($tpl_name);
        } elseif (is_array($tpl_name)) {
            foreach ($tpl_name as $tpl) {
                $file = $this->buildTplFilePath($tpl);
                if (is_file($file)) {
                    $this->now_rendering = $file;
                    break;
                } else {
                    $this->now_rendering = $file;
                }
            }
        } else {
            throw new \InvalidArgumentException(" ".__METHOD__."() expects parameter \$tpl_name to be string or array, ".gettype($tpl_name)." provided!");
        }

        // Remove the variables
        unset($tpl_data, $tpl_name, $tpl, $file);

        if (!is_file($this->now_rendering)) {
            throw new \LogicException("No template file found at {$this->now_rendering} ");
        }
        // Start the buffer
        ob_start();
        extract($this->templates_data);
        include $this->now_rendering;
        $output = ob_get_clean();

        if (!$echo_output) {
            return $output;
        }

        echo $output;
    }

    /**
     * Alias of self::render()
     *
     * @param  string|array  $tpl_name    Template name as string or in array
     *                                    for multiple fallback
     * @param  array   $tpl_data    Template data
     */
    public function insert($tpl_name, array $tpl_data = [])
    {
        $this->render($tpl_name, $tpl_data, true);
    }


    /**
     * Alias of self::render()
     *
     * @param  string|array  $tpl_name    Template name as string or in array
     *                                    for multiple fallback
     * @param  array   $tpl_data    Template data
     */
    public function extend($tpl_name, array $tpl_data = [])
    {
        $this->render($tpl_name, $tpl_data, true);
    }

    /**
     * Start recording a section
     *
     * @param  string $name The unique section name
     * @return
     */
    public function start($name)
    {
        // End any existing section recording instance first
        $this->end();

        ob_start();
        $this->started_section = $name;
    }

    /**
     * End recording a section, self::start() must be called before!
     *
     * @return
     */
    public function end()
    {
        if ($this->started_section && ob_get_level()) {
            $this->sections[$this->started_section] = ob_get_clean();
            $this->started_section = null;
        }
    }

    /**
     * Output recorded section
     *
     * @param  string $name           The unique section name
     * @param  string $empty_fallback Fallback text to display if section is empty
     *                                (Optional)
     * @return
     */
    public function section($name, $empty_fallback = '')
    {
        if (isset($this->sections[$name])) {
            echo $this->sections[$name];
        } else {
            echo $empty_fallback;
        }
    }

    /**
     * Alias of echo, with htmlentities() applied by default and support for multiple filters
     *
     * @param  mixed $var     The variable
     * @param  string $filters Pipe separated function names to apply on variable
     *                         Example: ( strip_tags|strtoupper|trim )
     * @return
     */
    public function e($var, $filters = '')
    {
        $var = $this->filter($var, $filters);
        echo htmlentities($var, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    }

    /**
     * Apply multiple filters to a variable
     *
     * @param  mixed $var     The variable
     * @param  string $filters Pipe separated function names to apply on variable
     *                         Example: ( strip_tags|strtoupper|trim )
     * @throws \RuntimeExecption If unable to call a filter function
     *
     * @return mixed
     */
    public function filter($var, $filters = null)
    {
        if (!$filters) {
            return $var;
        }

        foreach (explode('|', $filters) as $func) {
            if (is_callable($func)) {
                $var = call_user_func($func, $var);
            } else {
                throw new \RuntimeException("Unable to call function {$func}()");
            }
        }
        return $var;
    }

    public function exists($tpl_name)
    {
        $tpl_path = $this->buildTplFilePath($tpl_name);
        return is_file($tpl_path);
    }

    /**
     * Build absolute file path to a template
     *
     * @param  string $tpl_name The template name
     * @return string
     */
    protected function buildTplFilePath($tpl_name)
    {
        // Normalize windows directory separators
        $tpl_name = str_replace('\\', '/', $tpl_name);
        return $this->templates_dir . '/' . trim($tpl_name) . '.' . $this->templates_ext;
    }
}
