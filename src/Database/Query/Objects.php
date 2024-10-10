<?php

namespace Npds\Database\Query;

use Npds\Database\Connection;

/**
 * Undocumented class
 */
class Objects
{

    /**
     * [$sql description]
     *
     * @var [type]
     */
    protected $sql;

    /**
     * [$bindings description]
     *
     * @var [type]
     */
    protected $bindings = array();

    /**
     * [$connection description]
     *
     * @var [type]
     */
    protected $connection;


    /**
     * [__construct description]
     *
     * @param   [type]      $sql         [$sql description]
     * @param   array       $bindings    [$bindings description]
     * @param   Connection  $connection  [$connection description]
     *
     * @return  [type]                   [return description]
     */
    public function __construct($sql, array $bindings, Connection $connection)
    {
        $this->sql = (string)$sql;

        $this->bindings = $bindings;

        $this->connection = $connection;
    }

    /**
     * [getSql description]
     *
     * @return  [type]  [return description]
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * [getBindings description]
     *
     * @return  [type]  [return description]
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * [getRawSql description]
     *
     * @return  [type]  [return description]
     */
    public function getRawSql()
    {
        return $this->interpolateQuery($this->sql, $this->bindings);
    }

    /**
     * [interpolateQuery description]
     *
     * @param   [type]  $query   [$query description]
     * @param   [type]  $params  [$params description]
     *
     * @return  [type]           [return description]
     */
    protected function interpolateQuery($query, $params)
    {
        $keys = array();

        $values = $params;

        // Build a regular expression for each parameter
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:' . $key . '/';
            } else {
                $keys[] = '/[?]/';
            }

            if (is_string($value)) {
                $values[$key] = $this->connection->quote($value);
            }

            if (is_array($value)) {
                $values[$key] = implode(',', $this->connection->quote($value));
            }

            if (is_null($value)) {
                $values[$key] = 'NULL';
            }
        }

        $query = preg_replace($keys, $values, $query, 1, $count);

        return $query;
    }
    
}
