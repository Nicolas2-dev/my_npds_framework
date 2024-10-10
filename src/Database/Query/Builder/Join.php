<?php

namespace Npds\Database\Query\Builder;

use Npds\Database\Query\Builder as BaseBuilder;

/**
 * Undocumented class
 */
class Join extends BaseBuilder
{

    /**
     * [on description]
     *
     * @param   [type]  $key       [$key description]
     * @param   [type]  $operator  [$operator description]
     * @param   [type]  $value     [$value description]
     *
     * @return  [type]             [return description]
     */
    public function on($key, $operator, $value)
    {
        return $this->joinHandler($key, $operator, $value, 'AND');
    }

    /**
     * [orOn description]
     *
     * @param   [type]  $key       [$key description]
     * @param   [type]  $operator  [$operator description]
     * @param   [type]  $value     [$value description]
     *
     * @return  [type]             [return description]
     */
    public function orOn($key, $operator, $value)
    {
        return $this->joinHandler($key, $operator, $value, 'OR');
    }

    /**
     * [joinHandler description]
     *
     * @param   [type]$key       [$key description]
     * @param   [type]$operator  [$operator description]
     * @param   [type]$value     [$value description]
     * @param   [type]$joiner    [$joiner description]
     * @param   AND             [ description]
     *
     * @return  [type]          [return description]
     */
    protected function joinHandler($key, $operator = null, $value = null, $joiner = 'AND')
    {
        $key = $this->addTablePrefix($key);

        $value = $this->addTablePrefix($value);

        $this->statements['criteria'][] = compact('key', 'operator', 'value', 'joiner');

        return $this;
    }
    
}
