<?php

namespace Npds\Database\Query\Builder;

use Npds\Database\Query\Builder as BaseBuilder;

/**
 * Undocumented class
 */
class NestedCriteria extends BaseBuilder
{

    /**
     * [whereHandler description]
     *
     * @param   [type]$key       [$key description]
     * @param   [type]$operator  [$operator description]
     * @param   [type]$value     [$value description]
     * @param   [type]$joiner    [$joiner description]
     * @param   AND             [ description]
     *
     * @return  [type]          [return description]
     */
    protected function whereHandler($key, $operator = null, $value = null, $joiner = 'AND')
    {
        $key = $this->addTablePrefix($key);

        $this->statements['criteria'][] = compact('key', 'operator', 'value', 'joiner');

        return $this;
    }
    
}
