<?php

namespace Npds\Config;

/**
 * [Config description]
 */
class Config
{
    /**
     * [$options description]
     *
     * @var [type]
     */
    protected static $options = array();


    /**
     * [all description]
     *
     * @return  [type]  [return description]
     */
    public static function all()
    {
        return static::$options;
    }

    /**
     * [has description]
     *
     * @param   [type]  $key  [$key description]
     *
     * @return  [type]        [return description]
     */
    public static function has($key)
    {
        return array_has(static::$options, $key);
    }

    /**
     * [get description]
     *
     * @param   [type]  $key      [$key description]
     * @param   [type]  $default  [$default description]
     *
     * @return  [type]            [return description]
     */
    public static function get($key, $default = null)
    {
        return array_get(static::$options, $key, $default);
    }

    /**
     * [set description]
     *
     * @param   [type]  $key    [$key description]
     * @param   [type]  $value  [$value description]
     *
     * @return  [type]          [return description]
     */
    public static function set($key, $value)
    {
        array_set(static::$options, $key, $value);
    }

}
