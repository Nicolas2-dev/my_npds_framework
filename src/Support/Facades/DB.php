<?php

namespace Npds\Support\Facades;

use Npds\Database\Query\Builder as QueryBuilder;

/**
 * Undocumented class
 */
class DB
{
    /**
     * [__callStatic description]
     *
     * @param   [type]  $method      [$method description]
     * @param   [type]  $parameters  [$parameters description]
     *
     * @return  [type]               [return description]
     */
    public static function __callStatic($method, $parameters)
    {
        $instance = new QueryBuilder();

        return call_user_func_array(array($instance, $method), $parameters);
    }
    
}
