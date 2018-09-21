<?php
namespace Oishy\Core;

/**
* Whooks - Object Oriented Standalone Port of WordPress Hooks Library
*
* @version 0.1 Initial Release
* @author Miraz Mac <mirazmac@gmail.com>
* @package mirazmac\Whooks
* @link https://mirazmac.info Author Homepage
* @license LICENSE The MIT License
* @todo Write tests ( I wonder when I'll write 'em :3 )
*/
class Hook
{
    /**
     * $filters List of filters
     *
     * @var array
     */
    protected static $filters = [];

    /**
     * $merged_filters List of merged filters
     *
     * @var array
     */
    protected static $merged_filters = [];

    /**
     * $actions List of Actions
     *
     * @var array
     */
    protected static $actions = [];

    /**
     * $current_filter List of current filters
     *
     * @var array
     */
    protected static $current_filter = [];

    /**
     * Class constructor
     *
     * I'll just put it here :3
     */
    public function __construct()
    {
        self::$filters = [];
        self::$merged_filters = [];
        self::$actions = [];
        self::$current_filter = [];
    }


    /**
     * Hooks a function or method to a specific filter action.
     *
     * @param string $tag The name of the filter to hook the $function_to_add to.
     * @param callback $function_to_add The name of the function to be called when the filter is applied.
     * @param int $priority optional. Used to specify the order in which the functions associated with a particular
     *                                action are executed (default: 10). Lower numbers correspond with earlier execution,
     *                                and functions with the same priority are executed in the order in which they were
     *                                added to the action.
     * @param int $accepted_args optional. The number of arguments the function accept (default 1).
     * @return boolean true
     */
    public static function addFilter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        $id =  self::buildUniqueID($tag, $function_to_add, $priority);
        self::$filters[$tag][$priority][$id] = ['function' => $function_to_add, 'accepted_args' => $accepted_args];
        unset(self::$merged_filters[$tag]);
        return true;
    }

    /**
     * Removes a function from a specified filter hook.
     *
     * @param string $tag The filter hook to which the function to be removed is hooked.
     * @param callback $function_to_remove The name of the function which should be removed.
     * @param int $priority optional. The priority of the function (default: 10).
     * @param int $accepted_args optional. The number of arguments the function accepts (default: 1).
     * @return boolean Whether the function existed before it was removed.
     */
    public static function removeFilter($tag, $function_to_remove, $priority = 10)
    {
        $function_to_remove = self::buildUniqueID($tag, $function_to_remove, $priority);

        $function_exist = isset(self::$filters[$tag][$priority][$function_to_remove]);

        if ($function_exist) {
            unset(self::$filters[$tag][$priority][$function_to_remove]);
            if (empty(self::$filters[$tag][$priority])) {
                unset(self::$filters[$tag][$priority]);
            }
            unset(self::$merged_filters[$tag]);
        }
        return $function_exist;
    }

    /**
     * Remove all of the hooks from a filter.
     *
     * @param string $tag The filter to remove hooks from.
     * @param int $priority The priority number to remove.
     * @return boolean true when finished.
     */
    public static function removeAllFilters($tag, $priority = false)
    {
        if (isset(self::$filters[$tag])) {
            if (false !== $priority && isset(self::$filters[$tag][$priority])) {
                unset(self::$filters[$tag][$priority]);
            } else {
                unset(self::$filters[$tag]);
            }
        }

        if (isset(self::$merged_filters[$tag])) {
            unset(self::$merged_filters[$tag]);
        }

        return true;
    }

    /**
     * Check if any filter has been registered for a hook.
     *
     * @param string $tag The name of the filter hook.
     * @param callback $function_to_check optional.
     * @return mixed If $function_to_check is omitted, returns boolean for whether the hook has anything registered.
     *                  When checking a specific function, the priority of that hook is returned, or false if the function
     *                  is not attached. When using the $function_to_check argument, this function may return a non-boolean
     *                  value that evaluates to false (e.g.) 0, so use the === operator for testing the return value.
     */
    public static function hasFilter($tag, $function_to_check = false)
    {
        $has = !empty(self::$filters[$tag]);
        if (false === $function_to_check || false == $has) {
            return $has;
        }

        if (!$idx = self::buildUniqueID($tag, $function_to_check, false)) {
            return false;
        }

        foreach ((array)array_keys(self::$filters[$tag]) as $priority) {
            if (isset(self::$filters[$tag][$priority][$idx])) {
                return $priority;
            }
        }
        return false;
    }


    /**
     * Call the functions added to a filter hook.
     *
     * @param string $tag The name of the filter hook.
     * @param mixed $value The value on which the filters hooked to <tt>$tag</tt> are applied on.
     * @param mixed $var,... Additional variables passed to the functions hooked to <tt>$tag</tt>.
     * @return mixed The filtered value after all hooked functions are applied to it.
     */
    public static function applyFilters($tag, $value)
    {
        $args = [];

        // Do 'all' actions first
        if (isset(self::$filters['all'])) {
            self::$current_filter[] = $tag;
            $args = func_get_args();
            self::callAllHook($args);
        }

        if (!isset(self::$filters[$tag])) {
            if (isset(self::$filters['all'])) {
                array_pop(self::$current_filter);
            }
            return $value;
        }

        if (!isset(self::$filters['all'])) {
            self::$current_filter[] = $tag;
        }

        // Sort
        if (!isset(self::$merged_filters[$tag])) {
            ksort(self::$filters[$tag]);
            self::$merged_filters[$tag] = true;
        }

        reset(self::$filters[ $tag ]);

        if (empty($args)) {
            $args = func_get_args();
        }

        do {
            foreach ((array) current(self::$filters[$tag]) as $the_) {
                if (!is_null($the_['function'])) {
                    $args[1] = $value;
                    $value = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
                }
            }
        } while (next(self::$filters[$tag]) !== false);

        array_pop(self::$current_filter);

        return $value;
    }


    /**
     * Execute functions hooked on a specific filter hook, specifying arguments in an array.
     *
     * @param string $tag The name of the filter hook.
     * @param array $args The arguments supplied to the functions hooked to <tt>$tag</tt>
     * @return mixed The filtered value after all hooked functions are applied to it.
     */
    public static function applyFiltersRefArray($tag, $args)
    {
        // Do 'all' actions first
        if (isset(self::$filters['all'])) {
            self::$current_filter[] = $tag;
            $all_args = func_get_args();
            self::callAllHook($all_args);
        }

        if (!isset(self::$filters[$tag])) {
            if (isset(self::$filters['all'])) {
                array_pop(self::$current_filter);
            }
            return $args[0];
        }

        if (!isset(self::$filters['all'])) {
            self::$current_filter[] = $tag;
        }

        // Sort
        if (!isset(self::$merged_filters[ $tag ])) {
            ksort(self::$filters[$tag]);
            self::$merged_filters[ $tag ] = true;
        }

        reset(self::$filters[$tag]);

        do {
            foreach ((array) current(self::$filters[$tag]) as $the_) {
                if (!is_null($the_['function'])) {
                    $args[0] = call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));
                }
            }
        } while (next(self::$filters[$tag]) !== false);

        array_pop(self::$current_filter);

        return $args[0];
    }


    /**
     * Hooks a function on to a specific action.
     *
     * @param string $tag The name of the action to which the $function_to_add is hooked.
     * @param callback $function_to_add The name of the function you wish to be called.
     * @param int $priority optional. Used to specify the order in which the functions associated with a particular action
     *                      are executed (default: 10). Lower numbers correspond with earlier execution, and functions
     *                      with the same priority are executed in the order in which they were added to the action.
     * @param int $accepted_args optional. The number of arguments the function accept (default 1).
     * @return mixed
     */
    public static function addAction($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return self::addFilter($tag, $function_to_add, $priority, $accepted_args);
    }

    /**
     * Check if any action has been registered for a hook.
     *
     * @param string $tag The name of the action hook.
     * @param callback $function_to_check optional.
     * @return mixed If $function_to_check is omitted, returns boolean for whether the hook has anything registered.
     *               When checking a specific function, the priority of that hook is returned, or false if the function
     *               is not attached. When using the $function_to_check argument, this function may return a non-boolean
     *               value that evaluates to false. (e.g.) 0, so use the === operator for testing the return value.
     */
    public static function hasAction($tag, $function_to_check = false)
    {
        return self::hasFilter($tag, $function_to_check);
    }

    /**
     * Removes a function from a specified action hook.
     *
     * @param string $tag The action hook to which the function to be removed is hooked.
     * @param callback $function_to_remove The name of the function which should be removed.
     * @param int $priority optional The priority of the function (default: 10).
     * @return boolean Whether the function is removed.
     */
    public static function removeAction($tag, $function_to_remove, $priority = 10)
    {
        return self::removeFilter($tag, $function_to_remove, $priority);
    }

    /**
     * Remove all of the hooks from an action.
     *
     * @param string $tag The action to remove hooks from.
     * @param int $priority The priority number to remove them from.
     * @return bool True when finished.
     */
    public static function removeAllActions($tag, $priority = false)
    {
        return self::removeAllFilters($tag, $priority);
    }

    /**
     * Execute functions hooked on a specific action hook.
     *
     * @param string $tag The name of the action to be executed.
     * @param mixed $arg,... Optional additional arguments which are passed on to the functions hooked to the action.
     * @return null Will return null if $tag does not exist in $filter array
     */
    public static function doAction($tag, $arg = '')
    {
        if (!isset(self::$actions)) {
            self::$actions = [];
        }

        if (!isset(self::$actions[$tag])) {
            self::$actions[$tag] = 1;
        } else {
            ++self::$actions[$tag];
        }

        // Do 'all' actions first
        if (isset(self::$filters['all'])) {
            self::$current_filter[] = $tag;
            $all_args = func_get_args();
            self::callAllHook($all_args);
        }

        if (!isset(self::$filters[$tag])) {
            if (isset(self::$filters['all'])) {
                array_pop(self::$current_filter);
            }
            return;
        }

        if (!isset(self::$filters['all'])) {
            self::$current_filter[] = $tag;
        }

        $args = [];
        if (is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0])) {
            $args[] =& $arg[0];
        } else {
            $args[] = $arg;
        }
        for ($a = 2; $a < func_num_args(); $a++) {
            $args[] = func_get_arg($a);
        }

        if (!isset(self::$merged_filters[$tag])) {
            ksort(self::$filters[$tag]);
            self::$merged_filters[ $tag ] = true;
        }

        reset(self::$filters[$tag]);

        do {
            foreach ((array)current(self::$filters[$tag]) as $the_) {
                if (!is_null($the_['function'])) {
                    call_user_func_array($the_['function'], array_slice($args, 0, (int)$the_['accepted_args']));
                }
            }
        } while (next(self::$filters[$tag]) !== false);

        array_pop(self::$current_filter);
    }


    /**
     * Execute functions hooked on a specific action hook, specifying arguments in an array.
     *
     * @param string $tag The name of the action to be executed.
     * @param array $args The arguments supplied to the functions hooked to <tt>$tag</tt>
     * @return null Will return null if $tag does not exist in $filter array
     */
    public static function doActionRefArray($tag, $args)
    {
        if (!isset(self::$actions)) {
            self::$actions = [];
        }

        if (! isset(self::$actions[$tag])) {
            self::$actions[$tag] = 1;
        } else {
            ++self::$actions[$tag];
        }

        if (isset(self::$filters['all'])) {
            self::$current_filter[] = $tag;
            $all_args = func_get_args();
            self::callAllHook($all_args);
        }

        if (!isset(self::$filters[$tag])) {
            if (isset(self::$filters['all'])) {
                array_pop(self::$current_filter);
            }
            return;
        }

        if (!isset(self::$filters['all'])) {
            self::$current_filter[] = $tag;
        }

        if (!isset($merged_filters[ $tag ])) {
            ksort(self::$filters[$tag]);
            $merged_filters[$tag] = true;
        }

        reset(self::$filters[$tag]);

        do {
            foreach ((array) current(self::$filters[$tag]) as $the_) {
                if (!is_null($the_['function'])) {
                    call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));
                }
            }
        } while (next(self::$filters[$tag]) !== false);

        array_pop(self::$current_filter);
    }

    /**
     * Retrieve the number of times an action is fired.
     *
     * @param string $tag The name of the action hook.
     * @return int The number of times action hook <tt>$tag</tt> is fired
     */
    public static function didAction($tag)
    {
        if (!isset(self::$actions) || !isset(self::$actions[$tag])) {
            return 0;
        }

        return self::$actions[$tag];
    }

    /**
     * Retrieve the name of the current filter or action.
     *
     * @return string Hook name of the current filter or action.
     */
    public static function currentFilter()
    {
        return end(self::$current_filter);
    }


    /**
     * Retrieve the name of the current action.
     *
     * @return string Hook name of the current action.
     */
    public static function currentAction()
    {
        return self::currentFilter();
    }

    /**
     * Retrieve the name of a filter currently being processed.
     *
     * self::currentFilter() only returns the most recent filter or action
     * being executed. self::didAction() returns true once the action is initially
     * processed. This function allows detection for any filter currently being
     * executed (despite not being the most recent filter to fire, in the case of
     * hooks called from hook callbacks) to be verified.
     *
     * @param null|string $filter Optional. Filter to check. Defaults to null, which
     *                            checks if any filter is currently being run.
     * @return bool Whether the filter is currently in the stack
     */
    public static function doingFilter($filter = null)
    {
        if (null === $filter) {
            return !empty(self::$current_filter);
        }
        return in_array($filter, self::$current_filter);
    }

    /**
     * Retrieve the name of an action currently being processed.
     *
     * @param string|null $action Optional. Action to check. Defaults to null, which checks
     *                            if any action is currently being run.
     * @return bool Whether the action is currently in the stack.
     */
    public static function doingAction($action = null)
    {
        return self::doingFilter($action);
    }

    /**
     * Call all hooks
     *
     * @param  array $args Arguments
     */
    public static function callAllHook($args)
    {
        reset(self::$filters['all']);
        do {
            foreach ((array)current(self::$filters['all']) as $the_) {
                if (!is_null($the_['function'])) {
                    call_user_func_array($the_['function'], $args);
                }
            }
        } while (next(self::$filters['all']) !== false);
    }

    /**
     * Build Unique ID for storage and retrieval.
     *
     * @param string $tag Used in counting how many hooks were applied
     * @param callback $function Used for creating unique id
     * @param int|bool $priority Used in counting how many hooks were applied. If === false and $function is an object
     *                           reference, we return the unique id only if it already has one, false otherwise.
     * @return string|bool Unique ID for usage as array key or false if $priority === false and $function is an object
     *                     reference, and it does not already have a unique id.
     */
    private static function buildUniqueID($tag, $function, $priority)
    {
        static $filter_id_count = 0;

        if (is_string($function)) {
            return $function;
        }

        if (is_object($function)) {
            // Closures are currently implemented as objects
            $function = [$function, ''];
        } else {
            $function = (array)$function;
        }

        if (is_object($function[0])) {
            if (function_exists('spl_object_hash')) {
                return spl_object_hash($function[0]) . $function[1];
            } else {
                $obj_idx = get_class($function[0]).$function[1];
                if (!isset($function[0]->filter_id)) {
                    if (false === $priority) {
                        return false;
                    }
                    $obj_idx .= isset(self::$filters[$tag][$priority]) ? count((array)self::$filters[$tag][$priority]) : $filter_id_count;
                    $function[0]->filter_id = $filter_id_count;
                    ++$filter_id_count;
                } else {
                    $obj_idx .= $function[0]->filter_id;
                }

                return $obj_idx;
            }
        } elseif (is_string($function[0])) {
            // Static Calling
            return $function[0].$function[1];
        }
    }
}
