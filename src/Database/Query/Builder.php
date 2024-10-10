<?php

namespace Npds\Database\Query;

use Npds\Database\Connection;
use Npds\Database\Exception;
use Npds\Database\Manager as Database;
use Npds\Database\Query\Raw;
use Npds\Database\Query\Adapter;
use Npds\Database\Query\Objects as QueryObject;
use Npds\Database\Query\Builder\Join as JoinBuilder;
use Npds\Database\Query\Builder\Transaction;
use Npds\Database\Query\Builder\TransactionHaltException;
use PDO;

/**
 * Undocumented class
 */
class Builder
{

    /**
     * [$connection description]
     *
     * @var [type]
     */
    protected $connection;

    /**
     * [$statements description]
     *
     * @var [type]
     */
    protected $statements = array();

    /**
     * [$statement description]
     *
     * @var [type]
     */
    protected $statement = null;

    /**
     * [$tablePrefix description]
     *
     * @var [type]
     */
    protected $tablePrefix = null;

    /**
     * [$adapterInstance description]
     *
     * @var [type]
     */
    protected $adapterInstance;

    /**
     * [description]
     */
    protected $fetchParameters = array(\PDO::FETCH_OBJ);

    /**
     * [$lastResult description]
     *
     * @var [type]
     */
    protected $lastResult;


    /**
     * [__construct description]
     *
     * @param   Connection  $connection  [$connection description]
     *
     * @return  [type]                   [return description]
     */
    public function __construct(Connection $connection = null)
    {
        // Setup the Connection.
        if ($connection !== null) {
            $this->connection = $connection;
        } else {
            $this->connection = Database::getConnection();
        }

        // Setup the Table prefix.
        if (isset($this->adapterConfig['prefix'])) {
            $this->tablePrefix = $this->adapterConfig['prefix'];
        } else {
            $this->tablePrefix = DB_PREFIX;
        }

        // Setup the Query Adapter type.
        $this->adapter = $this->connection->getDriverCode();

        // Setup the Query Adapter options.
        $this->adapterConfig = $this->connection->getOptions();

        // Setup Query Adapter instance.
        $className = '\\Npds\\Database\\Query\\Adapter\\' . $this->adapter;

        $this->adapterInstance = new $className($this->connection);

        // Setup the Fetch Mode.
        $returnType = $this->connection->returnType();

        if ($returnType == 'assoc') {
            $this->setFetchMode(PDO::FETCH_ASSOC);
        } else if ($returnType == 'array') {
            $this->setFetchMode(PDO::FETCH_NUM);
        } else if ($returnType == 'object') {
            $this->setFetchMode(PDO::FETCH_OBJ);
        } else {
            $className = $returnType;

            // Check for a valid className.
            $classPath = str_replace('\\', '/', ltrim($className, '\\'));

            if (! preg_match('#^App(?:/Modules/.+)?/Models/(.*)$#i', $classPath)) {
                throw new \Exception(__d('system', 'No valid Model Name is given: {0}', $className));
            }

            if (! class_exists($className)) {
                throw new \Exception(__d('system', 'No valid Model Class is given: {0}', $className));
            }

            $this->setFetchMode(PDO::FETCH_CLASS, $className);
        }
    }

    /**
     * [setFetchMode description]
     *
     * @param   [type]  $mode  [$mode description]
     *
     * @return  [type]         [return description]
     */
    public function setFetchMode($mode)
    {
        $this->fetchParameters = func_get_args();

        return $this;
    }

    /**
     * [asAssoc description]
     *
     * @return  [type]  [return description]
     */
    public function asAssoc()
    {
        return $this->setFetchMode(PDO::FETCH_ASSOC);
    }

    /**
     * [asArray description]
     *
     * @return  [type]  [return description]
     */
    public function asArray()
    {
        return $this->setFetchMode(PDO::FETCH_NUM);
    }

    /**
     * [asObject description]
     *
     * @param   [type] $className        [$className description]
     * @param   array  $constructorArgs  [$constructorArgs description]
     * @param   array                    [ description]
     *
     * @return  [type]                   [return description]
     */
    public function asObject($className = null, array $constructorArgs = array())
    {
        if($className === null) {
            return $this->setFetchMode(PDO::FETCH_OBJ);
        }

        return $this->setFetchMode(PDO::FETCH_CLASS, $className, $constructorArgs);
    }

    /**
     * [newQuery description]
     *
     * @param   Connection  $connection  [$connection description]
     *
     * @return  [type]                   [return description]
     */
    public function newQuery(Connection $connection = null)
    {
        if (is_null($connection)) {
            $connection = $this->connection;
        }

        return new static($connection);
    }

    /**
     * [query description]
     *
     * @param   [type] $sql       [$sql description]
     * @param   [type] $bindings  [$bindings description]
     * @param   array             [ description]
     *
     * @return  [type]            [return description]
     */
    public function query($sql, $bindings = array())
    {
        list($this->statement) = $this->statement($sql, $bindings);

        return $this;
    }

    /**
     * [getStatement description]
     *
     * @return  [type]  [return description]
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * [getLastResult description]
     *
     * @return  [type]  [return description]
     */
    public function getLastResult()
    {
        return $this->lastResult;
    }

    /**
     * [statement description]
     *
     * @param   [type] $sql       [$sql description]
     * @param   [type] $bindings  [$bindings description]
     * @param   array             [ description]
     *
     * @return  [type]            [return description]
     */
    public function statement($sql, $bindings = array())
    {
        $start = microtime(true);

        $statement = $this->connection->prepare($sql);

        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_int($key) ? $key + 1 : $key,
                $value,
                (is_int($value) || is_bool($value)) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }

        $this->lastResult = $statement->execute();

        return array($statement, microtime(true) - $start);
    }

    /**
     * [get description]
     *
     * @return  [type]  [return description]
     */
    public function get()
    {
        $eventResult = $this->fireEvents('before-select');

        if (!is_null($eventResult)) {
            return $eventResult;
        };

        $executionTime = 0;

        if (is_null($this->statement)) {
            $queryObject = $this->getQuery('select');

            list($this->statement, $executionTime) = $this->statement(
                $queryObject->getSql(),
                $queryObject->getBindings()
            );
        }

        $start = microtime(true);

        $result = call_user_func_array(array($this->statement, 'fetchAll'), $this->fetchParameters);

        $executionTime += microtime(true) - $start;

        $this->statement = null;

        $this->fireEvents('after-select', $result, $executionTime);

        return $result;
    }

    /**
     * [first description]
     *
     * @return  [type]  [return description]
     */
    public function first()
    {
        $this->limit(1);

        $result = $this->get();

        return empty($result) ? null : array_shift($result);
    }

    /**
     * [findAll description]
     *
     * @param   [type]  $fieldName  [$fieldName description]
     * @param   [type]  $value      [$value description]
     *
     * @return  [type]              [return description]
     */
    public function findAll($fieldName, $value)
    {
        $this->where($fieldName, '=', $value);

        return $this->get();
    }

    /**
     * [findMany description]
     *
     * @param   [type]  $fieldName  [$fieldName description]
     * @param   [type]  $values     [$values description]
     *
     * @return  [type]              [return description]
     */
    public function findMany($fieldName, $values)
    {
        $this->whereIn($fieldName, $values);

        return $this->get();
    }

    /**
     * [find description]
     *
     * @param   [type]$value      [$value description]
     * @param   [type]$fieldName  [$fieldName description]
     * @param   id              [ description]
     *
     * @return  [type]          [return description]
     */
    public function find($value, $fieldName = 'id')
    {
        $this->where($fieldName, '=', $value);

        return $this->first();
    }

    /**
     * [count description]
     *
     * @return  [type]  [return description]
     */
    public function count()
    {
        // Get the current statements
        $originalStatements = $this->statements;

        unset($this->statements['orderBys']);
        unset($this->statements['limit']);
        unset($this->statements['offset']);

        $count = $this->aggregate('count');

        $this->statements = $originalStatements;

        return $count;
    }

    /**
     * [aggregate description]
     *
     * @param   [type]  $type  [$type description]
     *
     * @return  [type]         [return description]
     */
    protected function aggregate($type)
    {
        // Get the current selects
        $mainSelects = isset($this->statements['selects']) ? $this->statements['selects'] : null;

        // Replace select with a scalar value like `count`
        $this->statements['selects'] = array($this->raw($type . '(*) as field'));

        $row = $this->get();

        // Set the select as it was
        if ($mainSelects) {
            $this->statements['selects'] = $mainSelects;
        } else {
            unset($this->statements['selects']);
        }

        if (is_array($row[0])) {
            return (int) $row[0]['field'];
        } elseif (is_object($row[0])) {
            return (int) $row[0]->field;
        }

        return 0;
    }

    /**
     * [getQuery description]
     *
     * @param   [type]  $type            [$type description]
     * @param   select  $dataToBePassed  [$dataToBePassed description]
     * @param   array                    [ description]
     *
     * @return  [type]                   [return description]
     */
    public function getQuery($type = 'select', $dataToBePassed = array())
    {
        $allowedTypes = array(
            'select',
            'insert',
            'insertignore',
            'replace',
            'delete',
            'update',
            'criteriaonly'
        );

        if (! in_array(strtolower($type), $allowedTypes)) {
            throw new Exception($type . ' is not a known type.', 2);
        }

        $queryArr = $this->adapterInstance->$type($this->statements, $dataToBePassed);

        return new QueryObject($queryArr['sql'], $queryArr['bindings'], $this->connection);
    }

    /**
     * [subQuery description]
     *
     * @param   Builder  $queryBuilder  [$queryBuilder description]
     * @param   [type]   $alias         [$alias description]
     *
     * @return  [type]                  [return description]
     */
    public function subQuery(Builder $queryBuilder, $alias = null)
    {
        $sql = '(' . $queryBuilder->getQuery()->getRawSql() . ')';

        if ($alias) {
            $sql = $sql . ' as ' . $alias;
        }

        return $queryBuilder->raw($sql);
    }

    /**
     * [doInsert description]
     *
     * @param   [type]  $data  [$data description]
     * @param   [type]  $type  [$type description]
     *
     * @return  [type]         [return description]
     */
    private function doInsert($data, $type)
    {
        $eventResult = $this->fireEvents('before-insert');

        if (! is_null($eventResult)) {
            return $eventResult;
        }

        // If first value is not an array, it's not a batch insert
        if (! is_array(current($data))) {
            $queryObject = $this->getQuery($type, $data);

            list($result, $executionTime) = $this->statement($queryObject->getSql(), $queryObject->getBindings());

            $return = ($result->rowCount() === 1) ? $this->connection->lastInsertId() : false;
        } else {
            // Its a batch insert
            $return = array();

            $executionTime = 0;

            foreach ($data as $subData) {
                $queryObject = $this->getQuery($type, $subData);

                list($result, $time) = $this->statement($queryObject->getSql(), $queryObject->getBindings());

                $executionTime += $time;

                if ($result->rowCount() === 1) {
                    $return[] = $this->connection->lastInsertId();
                }
            }
        }

        $this->fireEvents('after-insert', $return, $executionTime);

        return $return;
    }

    /**
     * [insert description]
     *
     * @param   [type]  $data  [$data description]
     *
     * @return  [type]         [return description]
     */
    public function insert($data)
    {
        return $this->doInsert($data, 'insert');
    }

    /**
     * [insertIgnore description]
     *
     * @param   [type]  $data  [$data description]
     *
     * @return  [type]         [return description]
     */
    public function insertIgnore($data)
    {
        return $this->doInsert($data, 'insertignore');
    }

    /**
     * [replace description]
     *
     * @param   [type]  $data  [$data description]
     *
     * @return  [type]         [return description]
     */
    public function replace($data)
    {
        return $this->doInsert($data, 'replace');
    }

    /**
     * [update description]
     *
     * @param   [type]  $data  [$data description]
     *
     * @return  [type]         [return description]
     */
    public function update($data)
    {
        $eventResult = $this->fireEvents('before-update');

        if (! is_null($eventResult)) {
            return $eventResult;
        }

        $queryObject = $this->getQuery('update', $data);

        list($response, $executionTime) = $this->statement($queryObject->getSql(), $queryObject->getBindings());

        $this->fireEvents('after-update', $queryObject, $executionTime);

        if($this->lastResult !== false) {
            return $response->rowCount();
        }

        return false;
    }

    /**
     * [updateOrInsert description]
     *
     * @param   [type]  $data  [$data description]
     *
     * @return  [type]         [return description]
     */
    public function updateOrInsert($data)
    {
        if ($this->first()) {
            return $this->update($data);
        } else {
            return $this->insert($data);
        }
    }

    /**
     * [onDuplicateKeyUpdate description]
     *
     * @param   [type]  $data  [$data description]
     *
     * @return  [type]         [return description]
     */
    public function onDuplicateKeyUpdate($data)
    {
        $this->addStatement('onduplicate', $data);

        return $this;
    }

    /**
     * [delete description]
     *
     * @return  [type]  [return description]
     */
    public function delete()
    {
        $eventResult = $this->fireEvents('before-delete');

        if (! is_null($eventResult)) {
            return $eventResult;
        }

        $queryObject = $this->getQuery('delete');

        list($response, $executionTime) = $this->statement($queryObject->getSql(), $queryObject->getBindings());

        $this->fireEvents('after-delete', $queryObject, $executionTime);

        if($this->lastResult !== false) {
            return $response->rowCount();
        }

        return false;
    }

    /**
     * [table description]
     *
     * @param   [type]  $tables  [$tables description]
     *
     * @return  [type]           [return description]
     */
    public function table($tables)
    {
        if (! is_array($tables)) {
            // Because a single table is converted to an array anyways, this makes sense.
            $tables = func_get_args();
        }

        $instance = new static($this->connection);

        $tables = $this->addTablePrefix($tables, false);

        $instance->addStatement('tables', $tables);

        return $instance;
    }

    /**
     * [from description]
     *
     * @param   [type]  $tables  [$tables description]
     *
     * @return  [type]           [return description]
     */
    public function from($tables)
    {
        if (! is_array($tables)) {
            $tables = func_get_args();
        }

        $tables = $this->addTablePrefix($tables, false);

        $this->addStatement('tables', $tables);

        return $this;
    }

    /**
     * [select description]
     *
     * @param   [type]  $fields  [$fields description]
     *
     * @return  [type]           [return description]
     */
    public function select($fields)
    {
        if (! is_array($fields)) {
            $fields = func_get_args();
        }

        $fields = $this->addTablePrefix($fields);

        $this->addStatement('selects', $fields);

        return $this;
    }

    /**
     * [selectDistinct description]
     *
     * @param   [type]  $fields  [$fields description]
     *
     * @return  [type]           [return description]
     */
    public function selectDistinct($fields)
    {
        $this->select($fields);

        $this->addStatement('distinct', true);

        return $this;
    }

    /**
     * [groupBy description]
     *
     * @param   [type]  $field  [$field description]
     *
     * @return  [type]          [return description]
     */
    public function groupBy($field)
    {
        $field = $this->addTablePrefix($field);

        $this->addStatement('groupBys', $field);

        return $this;
    }

    /**
     * [orderBy description]
     *
     * @param   [type]$fields            [$fields description]
     * @param   [type]$defaultDirection  [$defaultDirection description]
     * @param   ASC                     [ description]
     *
     * @return  [type]                  [return description]
     */
    public function orderBy($fields, $defaultDirection = 'ASC')
    {
        if (!is_array($fields)) {
            $fields = array($fields);
        }

        foreach ($fields as $key => $value) {
            $field = $key;

            $type = $value;

            if (is_int($key)) {
                $field = $value;

                $type = $defaultDirection;
            }

            if (! $field instanceof Raw) {
                $field = $this->addTablePrefix($field);
            }

            $this->statements['orderBys'][] = compact('field', 'type');
        }

        return $this;
    }

    /**
     * [limit description]
     *
     * @param   [type]  $limit  [$limit description]
     *
     * @return  [type]          [return description]
     */
    public function limit($limit)
    {
        $this->statements['limit'] = $limit;

        return $this;
    }

    /**
     * [offset description]
     *
     * @param   [type]  $offset  [$offset description]
     *
     * @return  [type]           [return description]
     */
    public function offset($offset)
    {
        $this->statements['offset'] = $offset;

        return $this;
    }

    /**
     * [having description]
     *
     * @param   [type]$key       [$key description]
     * @param   [type]$operator  [$operator description]
     * @param   [type]$value     [$value description]
     * @param   [type]$joiner    [$joiner description]
     * @param   AND             [ description]
     *
     * @return  [type]          [return description]
     */
    public function having($key, $operator, $value, $joiner = 'AND')
    {
        $key = $this->addTablePrefix($key);

        $this->statements['havings'][] = compact('key', 'operator', 'value', 'joiner');

        return $this;
    }

    /**
     * [orHaving description]
     *
     * @param   [type]  $key       [$key description]
     * @param   [type]  $operator  [$operator description]
     * @param   [type]  $value     [$value description]
     *
     * @return  [type]             [return description]
     */
    public function orHaving($key, $operator, $value)
    {
        return $this->having($key, $operator, $value, 'OR');
    }

    /**
     * [where description]
     *
     * @param   [type]  $key       [$key description]
     * @param   [type]  $operator  [$operator description]
     * @param   [type]  $value     [$value description]
     *
     * @return  [type]             [return description]
     */
    public function where($key, $operator = null, $value = null)
    {
        // If two params are given then assume operator is =
        if (func_num_args() == 2) {
            $value = $operator;

            $operator = '=';
        }

        return $this->whereHandler($key, $operator, $value);
    }

    /**
     * [orWhere description]
     *
     * @param   [type]  $key       [$key description]
     * @param   [type]  $operator  [$operator description]
     * @param   [type]  $value     [$value description]
     *
     * @return  [type]             [return description]
     */
    public function orWhere($key, $operator = null, $value = null)
    {
        // If two params are given then assume operator is =
        if (func_num_args() == 2) {
            $value = $operator;

            $operator = '=';
        }

        return $this->whereHandler($key, $operator, $value, 'OR');
    }

    /**
     * [whereNot description]
     *
     * @param   [type]  $key       [$key description]
     * @param   [type]  $operator  [$operator description]
     * @param   [type]  $value     [$value description]
     *
     * @return  [type]             [return description]
     */
    public function whereNot($key, $operator = null, $value = null)
    {
        // If two params are given then assume operator is =
        if (func_num_args() == 2) {
            $value = $operator;

            $operator = '=';
        }

        return $this->whereHandler($key, $operator, $value, 'AND NOT');
    }

    /**
     * [orWhereNot description]
     *
     * @param   [type]  $key       [$key description]
     * @param   [type]  $operator  [$operator description]
     * @param   [type]  $value     [$value description]
     *
     * @return  [type]             [return description]
     */
    public function orWhereNot($key, $operator = null, $value = null)
    {
        // If two params are given then assume operator is =
        if (func_num_args() == 2) {
            $value = $operator;

            $operator = '=';
        }
        return $this->whereHandler($key, $operator, $value, 'OR NOT');
    }

    /**
     * [whereIn description]
     *
     * @param   [type]  $key     [$key description]
     * @param   [type]  $values  [$values description]
     *
     * @return  [type]           [return description]
     */
    public function whereIn($key, $values)
    {
        return $this->whereHandler($key, 'IN', $values, 'AND');
    }

    /**
     * [whereNotIn description]
     *
     * @param   [type]  $key     [$key description]
     * @param   [type]  $values  [$values description]
     *
     * @return  [type]           [return description]
     */
    public function whereNotIn($key, $values)
    {
        return $this->whereHandler($key, 'NOT IN', $values, 'AND');
    }

    /**
     * [orWhereIn description]
     *
     * @param   [type]  $key     [$key description]
     * @param   [type]  $values  [$values description]
     *
     * @return  [type]           [return description]
     */
    public function orWhereIn($key, $values)
    {
        return $this->whereHandler($key, 'IN', $values, 'OR');
    }

    /**
     * [orWhereNotIn description]
     *
     * @param   [type]  $key     [$key description]
     * @param   [type]  $values  [$values description]
     *
     * @return  [type]           [return description]
     */
    public function orWhereNotIn($key, $values)
    {
        return $this->whereHandler($key, 'NOT IN', $values, 'OR');
    }

    /**
     * [whereBetween description]
     *
     * @param   [type]  $key        [$key description]
     * @param   [type]  $valueFrom  [$valueFrom description]
     * @param   [type]  $valueTo    [$valueTo description]
     *
     * @return  [type]              [return description]
     */
    public function whereBetween($key, $valueFrom, $valueTo)
    {
        return $this->whereHandler($key, 'BETWEEN', array($valueFrom, $valueTo), 'AND');
    }

    /**
     * [orWhereBetween description]
     *
     * @param   [type]  $key        [$key description]
     * @param   [type]  $valueFrom  [$valueFrom description]
     * @param   [type]  $valueTo    [$valueTo description]
     *
     * @return  [type]              [return description]
     */
    public function orWhereBetween($key, $valueFrom, $valueTo)
    {
        return $this->whereHandler($key, 'BETWEEN', array($valueFrom, $valueTo), 'OR');
    }

    /**
     * [whereNull description]
     *
     * @param   [type]  $key  [$key description]
     *
     * @return  [type]        [return description]
     */
    public function whereNull($key)
    {
        return $this->whereNullHandler($key);
    }

    /**
     * [whereNotNull description]
     *
     * @param   [type]  $key  [$key description]
     *
     * @return  [type]        [return description]
     */
    public function whereNotNull($key)
    {
        return $this->whereNullHandler($key, 'NOT');
    }

    /**
     * [orWhereNull description]
     *
     * @param   [type]  $key  [$key description]
     *
     * @return  [type]        [return description]
     */
    public function orWhereNull($key)
    {
        return $this->whereNullHandler($key, '', 'or');
    }

    /**
     * [orWhereNotNull description]
     *
     * @param   [type]  $key  [$key description]
     *
     * @return  [type]        [return description]
     */
    public function orWhereNotNull($key)
    {
        return $this->whereNullHandler($key, 'NOT', 'or');
    }

    /**
     * [whereNullHandler description]
     *
     * @param   [type]  $key       [$key description]
     * @param   [type]  $prefix    [$prefix description]
     * @param   [type]  $operator  [$operator description]
     *
     * @return  [type]             [return description]
     */
    protected function whereNullHandler($key, $prefix = '', $operator = '')
    {
        $key = $this->adapterInstance->wrapSanitizer($this->addTablePrefix($key));

        return $this->{$operator . 'Where'}($this->raw("{$key} IS {$prefix} NULL"));
    }

    /**
     * [join description]
     *
     * @param   [type] $table     [$table description]
     * @param   [type] $key       [$key description]
     * @param   [type] $operator  [$operator description]
     * @param   [type] $value     [$value description]
     * @param   [type] $type      [$type description]
     * @param   inner             [ description]
     *
     * @return  [type]            [return description]
     */
    public function join($table, $key, $operator = null, $value = null, $type = 'inner')
    {
        if (!$key instanceof \Closure) {
            $key = function ($joinBuilder) use ($key, $operator, $value) {
                $joinBuilder->on($key, $operator, $value);
            };
        }

        // Build a new JoinBuilder class, keep it by reference so any changes made in the closure should reflect here.
        $joinBuilder = new JoinBuilder($this->connection);

        $joinBuilder = & $joinBuilder;

        // Call the closure with our new joinBuilder object
        $key($joinBuilder);

        $table = $this->addTablePrefix($table, false);

        // Get the criteria only query from the joinBuilder object
        $this->statements['joins'][] = compact('type', 'table', 'joinBuilder');

        return $this;
    }

    /**
     * [description]
     */
    public function transaction(\Closure $callback)
    {
        try {
            // Begin the PDO transaction
            $this->connection->beginTransaction();

            // Get the Transaction class
            $transaction = new Transaction($this->connection);

            // Call closure
            $callback($transaction);

            // If no errors have been thrown or the transaction wasn't completed within
            // the closure, commit the changes
            $this->connection->commit();

            return $this;
        } catch (TransactionHaltException $e) {
            // Commit or rollback behavior has been handled in the closure, so exit
            return $this;
        } catch (\Exception $e) {
            // something happened, rollback changes
            $this->connection->rollBack();

            return $this;
        }
    }

    /**
     * [leftJoin description]
     *
     * @param   [type]  $table     [$table description]
     * @param   [type]  $key       [$key description]
     * @param   [type]  $operator  [$operator description]
     * @param   [type]  $value     [$value description]
     *
     * @return  [type]             [return description]
     */
    public function leftJoin($table, $key, $operator = null, $value = null)
    {
        return $this->join($table, $key, $operator, $value, 'left');
    }

    /**
     * [rightJoin description]
     *
     * @param   [type]  $table     [$table description]
     * @param   [type]  $key       [$key description]
     * @param   [type]  $operator  [$operator description]
     * @param   [type]  $value     [$value description]
     *
     * @return  [type]             [return description]
     */
    public function rightJoin($table, $key, $operator = null, $value = null)
    {
        return $this->join($table, $key, $operator, $value, 'right');
    }

    /**
     * [innerJoin description]
     *
     * @param   [type]  $table     [$table description]
     * @param   [type]  $key       [$key description]
     * @param   [type]  $operator  [$operator description]
     * @param   [type]  $value     [$value description]
     *
     * @return  [type]             [return description]
     */
    public function innerJoin($table, $key, $operator = null, $value = null)
    {
        return $this->join($table, $key, $operator, $value, 'inner');
    }

    /**
     * [raw description]
     *
     * @param   [type] $value     [$value description]
     * @param   [type] $bindings  [$bindings description]
     * @param   array             [ description]
     *
     * @return  [type]            [return description]
     */
    public function raw($value, $bindings = array())
    {
        return new Raw($value, $bindings);
    }

    /**
     * [connection description]
     *
     * @return  [type]  [return description]
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * [setConnection description]
     *
     * @param   Connection  $connection  [$connection description]
     *
     * @return  [type]                   [return description]
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * [getConnection description]
     *
     * @return  [type]  [return description]
     */
    public function getConnection()
    {
        return $this->connection;
    }

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

        $this->statements['wheres'][] = compact('key', 'operator', 'value', 'joiner');

        return $this;
    }

    /**
     * [addTablePrefix description]
     *
     * @param   [type]$values         [$values description]
     * @param   [type]$tableFieldMix  [$tableFieldMix description]
     * @param   true                  [ description]
     *
     * @return  [type]                [return description]
     */
    public function addTablePrefix($values, $tableFieldMix = true)
    {
        if (is_null($this->tablePrefix)) {
            return $values;
        }

        // $value will be an array and we will add prefix to all table names

        // If supplied value is not an array then make it one
        $single = false;

        if (! is_array($values)) {
            $values = array($values);
            // We had single value, so should return a single value
            $single = true;
        }

        $return = array();

        foreach ($values as $key => $value) {
            // It's a raw query, just add it to our return array and continue next
            if (($value instanceof Raw) || ($value instanceof \Closure)) {
                $return[$key] = $value;

                continue;
            }

            // If key is not integer, it is likely a alias mapping,
            // so we need to change prefix target
            $target = &$value;

            if (! is_integer($key)) {
                $target = &$key;
            }

            if (! $tableFieldMix || ($tableFieldMix && strpos($target, '.') !== false)) {
                $target = $this->tablePrefix . $target;
            }

            $return[$key] = $value;
        }

        // If we had single value then we should return a single value (end value of the array)
        return $single ? end($return) : $return;
    }

    /**
     * [addStatement description]
     *
     * @param   [type]  $key    [$key description]
     * @param   [type]  $value  [$value description]
     *
     * @return  [type]          [return description]
     */
    protected function addStatement($key, $value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }

        if (!array_key_exists($key, $this->statements)) {
            $this->statements[$key] = $value;
        } else {
            $this->statements[$key] = array_merge($this->statements[$key], $value);
        }
    }

    /**
     * [getEvent description]
     *
     * @param   [type]  $event  [$event description]
     * @param   [type]  $table  [$table description]
     *
     * @return  [type]          [return description]
     */
    public function getEvent($event, $table = ':any')
    {
        return $this->connection->getEventHandler()->getEvent($event, $table);
    }

    /**
     * [description]
     */
    public function registerEvent($event, $table, \Closure $action)
    {
        $table = $table ?: ':any';

        if ($table != ':any') {
            $table = $this->addTablePrefix($table, false);
        }

        return $this->connection->getEventHandler()->registerEvent($event, $table, $action);
    }

    /**
     * [removeEvent description]
     *
     * @param   [type]  $event  [$event description]
     * @param   [type]  $table  [$table description]
     *
     * @return  [type]          [return description]
     */
    public function removeEvent($event, $table = ':any')
    {
        if ($table != ':any') {
            $table = $this->addTablePrefix($table, false);
        }

        return $this->connection->getEventHandler()->removeEvent($event, $table);
    }

    /**
     * [fireEvents description]
     *
     * @param   [type]  $event  [$event description]
     *
     * @return  [type]          [return description]
     */
    public function fireEvents($event)
    {
        $params = func_get_args();

        array_unshift($params, $this);

        return call_user_func_array(array($this->connection->getEventHandler(), 'fireEvents'), $params);
    }

    /**
     * [getStatements description]
     *
     * @return  [type]  [return description]
     */
    public function getStatements()
    {
        return $this->statements;
    }
    
}
