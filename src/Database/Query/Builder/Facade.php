<?php

namespace Npds\Database\Query\Builder;

use Npds\Database\Query\Builder as QueryBuilder;

/**
 * Undocumented class
 */
class Facade
{

    /**
     * [$builderInstance description]
     *
     * @var [type]
     */
    protected static $builderInstance;


    /**
     * [__callStatic description]
     *
     * @param   [type]  $method  [$method description]
     * @param   [type]  $args    [$args description]
     *
     * @return  [type]           [return description]
     */
    public static function __callStatic($method, $args)
    {
        if (!static::$builderInstance) {
            static::$builderInstance = new QueryBuilder();
        }

        // Call the non-static method from the class instance
        return call_user_func_array(array(static::$builderInstance, $method), $args);
    }

    /**
     * [setInstance description]
     *
     * @param   QueryBuilder  $queryBuilder  [$queryBuilder description]
     *
     * @return  [type]                       [return description]
     */
    public static function setInstance(QueryBuilder $queryBuilder)
    {
        static::$builderInstance = $queryBuilder;
    }

}
