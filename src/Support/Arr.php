<?php

namespace Npds\Support;

/**
 * Undocumented class
 */
class Arr
{
    
    /**
     * Undocumented function
     *
     * @param array $array
     * @param [type] $path
     * @param [type] $value
     * @return void
     */
    public static function set(array &$array, $path, $value)
    {
        $segments = explode('.', $path);
        while (count($segments) > 1) {
            $segment = array_shift($segments);
            if (!isset($array[$segment]) || !is_array($array[$segment])) {
                $array[$segment] = [];
            }
            $array =& $array[$segment];
        }
        $array[array_shift($segments)] = $value;
    }

    /**
     * Undocumented function
     *
     * @param array $array
     * @param [type] $path
     * @return boolean
     */
    public static function has(array $array, $path)
    {
        $segments = explode('.', $path);
        foreach ($segments as $segment) {
            if (!is_array($array) || !isset($array[$segment])) {
                return false;
            }
            $array = $array[$segment];
        }

        return true;
    }

    /**
     * Undocumented function
     *
     * @param array $array
     * @param [type] $path
     * @param [type] $default
     * @return void
     */
    public static function get(array $array, $path, $default = null)
    {
        $segments = explode('.', $path);
        foreach ($segments as $segment) {
            if (!is_array($array) || !isset($array[$segment])) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Undocumented function
     *
     * @param array $array
     * @param [type] $path
     * @return void
     */
    public static function remove(array &$array, $path)
    {
        $segments = explode('.', $path);
        while (count($segments) > 1) {
            $segment = array_shift($segments);
            if (!isset($array[$segment]) || !is_array($array[$segment])) {
                return false;
            }
            $array =& $array[$segment];
        }
        unset($array[array_shift($segments)]);

        return true;
    }

    /**
     * Undocumented function
     *
     * @param array $array
     * @return void
     */
    public static function rand(array $array)
    {
        return $array[array_rand($array)];
    }

    /**
     * Undocumented function
     *
     * @param array $array
     * @return boolean
     */
    public static function isAssoc(array $array)
    {
        return count(array_filter(array_keys($array), 'is_string')) === count($array);
    }

    /**
     * Undocumented function
     *
     * @param array $array
     * @param [type] $key
     * @return void
     */
    public static function value(array $array, $key)
    {
        return array_map(function ($value) use ($key) {
            return is_object($value) ? $value->$key : $value[$key];
        }, $array);
    }
    
}
