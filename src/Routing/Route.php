<?php

namespace Npds\Routing;

/**
 * Undocumented class
 */
class Route
{
    /**
     * [$availFilters description]
     *
     * @var [type]
     */
    private static $availFilters = array();

    /**
     * [$methods description]
     *
     * @var [type]
     */
    private $methods = array();

    /**
     * [$pattern description]
     *
     * @var [type]
     */
    private $pattern;

    /**
     * [$filters description]
     *
     * @var [type]
     */
    private $filters = array();

    /**
     * [$callback description]
     *
     * @var [type]
     */
    private $callback = null;

    /**
     * [$currentUri description]
     *
     * @var [type]
     */
    private $currentUri = null;

    /**
     * [$method description]
     *
     * @var [type]
     */
    private $method = null;

    /**
     * [$params description]
     *
     * @var [type]
     */
    private $params = array();

    /**
     * [$regex description]
     *
     * @var [type]
     */
    private $regex;


    /**
     * [__construct description]
     *
     * @param   [type]  $method    [$method description]
     * @param   [type]  $pattern   [$pattern description]
     * @param   [type]  $callback  [$callback description]
     *
     * @return  [type]             [return description]
     */
    public function __construct($method, $pattern, $callback)
    {
        $this->methods = array_map('strtoupper', is_array($method) ? $method : array($method));

        $this->pattern = ! empty($pattern) ? $pattern : '/';

        if(is_array($callback)) {
            $this->callback = isset($callback['uses']) ? $callback['uses'] : null;

            if(isset($callback['filters']) && ! empty($callback['filters'])) {
                // Explode the filters string using the '|' delimiter.
                $filters = array_filter(explode('|', $callback['filters']), 'strlen');

                $this->filters = array_unique($filters);
            }
        } else {
            $this->callback = $callback;
        }
    }

    /**
     * [filter description]
     *
     * @param   [type]  $name      [$name description]
     * @param   [type]  $callback  [$callback description]
     *
     * @return  [type]             [return description]
     */
    public static function filter($name, $callback)
    {
        self::$availFilters[$name] = $callback;
    }

    /**
     * [availFilters description]
     *
     * @return  [type]  [return description]
     */
    public static function availFilters()
    {
        return self::$availFilters;
    }

    /**
     * [applyFilters description]
     *
     * @return  [type]  [return description]
     */
    public function applyFilters()
    {
        $result = true;

        foreach ($this->filters as $filter) {
            if(array_key_exists($filter, self::$availFilters)) {
                // Get the current Filter Callback.
                $callback = self::$availFilters[$filter];

                // Execute the current Filter's callback with the current matched Route as argument.
                //
                // When the Filter returns false, the filtering is considered being globally failed.
                if($callback !== null) {
                    $result = $this->invokeCallback($callback);
                }
            } else {
                // No Filter with this name found; mark that as failure.
                $result = false;
            }

            if($result === false) {
                // Failure of the current Filter; stop the loop.
                break;
            }
        }

        return $result;
    }

    /**
     * [invokeCallback description]
     *
     * @param   [type]  $callback  [$callback description]
     *
     * @return  [type]             [return description]
     */
    private function invokeCallback($callback)
    {
        if (is_object($callback)) {
            // We have a Closure; execute it with the Route instance as parameter.
            return call_user_func($callback, $this);
        }

        // Extract the Class name and the Method from the callback's string.
        $segments = explode('@', $callback);

        $className = $segments[0];
        $method    = $segments[1];

        if (! class_exists($className)) {
            return false;
        }

        // The Filter Class receive on Constructor the Route instance as parameter.
        $object = new $className();

        if (method_exists($object, $method)) {
            // Execute the object's method with this Route instance as argument.
            return call_user_func(array($object, $method), $this);
        }

        return false;
    }

    /**
     * [match description]
     *
     * @param   [type]$uri        [$uri description]
     * @param   [type]$method     [$method description]
     * @param   [type]$optionals  [$optionals description]
     * @param   true              [ description]
     *
     * @return  [type]            [return description]
     */
    public function match($uri, $method, $optionals = true)
    {
        if (! in_array($method, $this->methods)) {
            return false;
        }

        // Have a valid HTTP method for this Route; store it for later usage.
        $this->method = $method;

        // Exact match Route.
        if ($this->pattern == $uri) {
            // Store the current matched URI.
            $this->currentUri = $uri;

            return true;
        }

        // Build the regex for matching.
        if (strpos($this->pattern, ':') !== false) {
            $regex = str_replace(array(':any', ':num', ':all'), array('[^/]+', '[0-9]+', '.*'), $this->pattern);
        } else {
            $regex = $this->pattern;
        }

        if ($optionals !== false) {
            $searches = array('(/', ')');
            $replaces = array('(?:/', ')?');

            if (is_array($optionals) && ! empty($optionals)) {
                $searches = array_merge(array_keys($optionals), $searches);
                $replaces = array_merge(array_values($optionals), $replaces);
            }

            $regex = str_replace($searches, $replaces, $regex);
        }

        // Attempt to match the Route and extract the parameters.
        if (preg_match('#^' .$regex .'(?:\?.*)?$#i', $uri, $matches)) {
            // Remove $matched[0] as [1] is the first parameter.
            array_shift($matches);

            // Store the current matched URI.
            $this->currentUri = $uri;

            // Store the extracted parameters.
            $this->params = $matches;

            // Also, store the compiled Regex.
            $this->regex = $regex;

            return true;
        }

        return false;
    }

    /**
     * [methods description]
     *
     * @return  [type]  [return description]
     */
    public function methods()
    {
        return $this->methods;
    }

    /**
     * [pattern description]
     *
     * @return  [type]  [return description]
     */
    public function pattern()
    {
        return $this->pattern;
    }

    /**
     * [filters description]
     *
     * @return  [type]  [return description]
     */
    public function filters()
    {
        return $this->filters;
    }

    /**
     * [callback description]
     *
     * @return  [type]  [return description]
     */
    public function callback()
    {
        return $this->callback;
    }

    /**
     * [currentUri description]
     *
     * @return  [type]  [return description]
     */
    public function currentUri()
    {
        return $this->currentUri;
    }

    /**
     * [method description]
     *
     * @return  [type]  [return description]
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * [params description]
     *
     * @return  [type]  [return description]
     */
    public function params()
    {
        return $this->params;
    }

    /**
     * [regex description]
     *
     * @return  [type]  [return description]
     */
    public function regex()
    {
        return $this->regex;
    }
    
}
