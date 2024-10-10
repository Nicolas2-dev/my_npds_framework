<?php

namespace Npds\Support;

use Npds\Database\Manager;
use Npds\Database\Connection;

/**
 * Undocumented class
 */
class Database
{

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    protected $connection = null;

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected static $instances = array();


    /**
     * Undocumented function
     *
     * @param boolean $linkName
     * @return void
     */
    public static function get($linkName = false)
    {
        if (is_array($linkName)) {
            throw new \Exception(__d('system', 'Invalid Configuration on the Legacy Helper'));
        }

        // Adjust the linkName value, if case.
        $linkName = $linkName ? $linkName : 'default';

        // Checking if the same
        if (isset(self::$instances[$linkName])) {
            return self::$instances[$linkName];
        }

        $instance = new Database($linkName);

        // Setting Database into $instances to avoid duplication
        self::$instances[$linkName] = $instance;

        return $instance;
    }

    /**
     * Undocumented function
     *
     * @param [type] $linkName
     */
    protected function __construct($linkName)
    {
        $this->connection = Manager::getConnection($linkName);
    }

    /**
     * Undocumented function
     *
     * @param [type] $sql
     * @return void
     */
    public function raw($sql)
    {
        return $this->connection->rawQuery($sql);
    }

    /**
     * Undocumented function
     *
     * @param [type] $sql
     * @param array $array
     * @param [type] $fetchMode
     * @param string $class
     * @return void
     */
    public function select($sql, $array = array(), $fetchMode = PDO::FETCH_OBJ, $class = '')
    {
        if ($fetchMode == PDO::FETCH_OBJ) {
            $returnType = 'object';
        } else if ($fetchMode == PDO::FETCH_CLASS) {
            if (empty($class)) {
                throw new \Exception(__d('system', 'No valid Class is given'));
            }

            $returnType = $class;
        } else {
            $returnType = 'array';
        }

        // Pre-process the $array to simulate the make the old Helper behavior.
        $where = array();
        $paramTypes = array();

        foreach ($array as $field => $value) {
            // Strip the character ':', if it exists in the first position of $field.
            if (substr($field, 0, 1) == ':') {
                $field = substr($field, 1);
            }

            $where[$field] = $value;

            // Prepare the old style entry into paramTypes.
            $paramTypes[$field] = is_integer($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        }

        return $this->connection->select($sql, $where, $paramTypes, $returnType, true);
    }

    /**
     * Undocumented function
     *
     * @param [type] $table
     * @param [type] $data
     * @return void
     */
    public function insert($table, $data)
    {
        ksort($data);

        // Pre-process the $data variable to simulate the make the old Helper behavior.
        $paramTypes = array();

        foreach ($data as $field => $value) {
            // Prepare the compat entry into paramTypes.
            $paramTypes[$field] = is_integer($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        }

        return $this->connection->insert($table, $data, $paramTypes);
    }

    /**
     * Undocumented function
     *
     * @param [type] $table
     * @param [type] $data
     * @param [type] $where
     * @return void
     */
    public function update($table, $data, $where)
    {
        ksort($data);

        // Pre-process the $data and $where variables to simulate the old Helper behavior.
        $paramTypes = array();

        foreach ($data as $field => $value) {
            // Prepare the compat entry into paramTypes.
            $paramTypes[$field] = is_integer($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        }

        foreach ($where as $field => $value) {
            // Prepare the compat entry into paramTypes.
            $paramTypes[$field] = is_integer($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        }

        return $this->connection->update($table, $data, $where, $paramTypes);
    }

    /**
     * Undocumented function
     *
     * @param [type] $table
     * @param [type] $where
     * @param integer $limit
     * @return void
     */
    public function delete($table, $where, $limit = 1)
    {
        ksort($where);

        // Pre-process the $where variable to simulate the old Helper behavior.
        $paramTypes = array();

        foreach ($where as $field => $value) {
            // Prepare the compat entry into paramTypes.
            $paramTypes[$field] = is_integer($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        }

        return $this->connection->delete($table, $where, $paramTypes);
    }

    /**
     * Undocumented function
     *
     * @param [type] $table
     * @return void
     */
    public function truncate($table)
    {
        return $this->connection->truncate($table);
    }

    /**
     * Undocumented function
     *
     * @param [type] $method
     * @param [type] $params
     * @return void
     */
    public function __call($method, $params = null)
    {
        if (method_exists($this->connection, $method)) {
            return call_user_func_array(array($this->connection, $method), $params);
        }
    }
    
}
