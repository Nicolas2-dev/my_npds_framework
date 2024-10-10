<?php

namespace Npds\ORM;

use Npds\Database\Connection;
use Npds\Database\Manager as Database;
use Npds\ORM\Model;

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
    protected $connection = 'default';

    /**
     * [$db description]
     *
     * @var [type]
     */
    protected $db = null;

    /**
     * [$query description]
     *
     * @var [type]
     */
    protected $query = null;

    /**
     * [$model description]
     *
     * @var [type]
     */
    protected $model = null;

    /**
     * [$table description]
     *
     * @var [type]
     */
    protected $table;

    /**
     * [$primaryKey description]
     *
     * @var [type]
     */
    protected $primaryKey;

    /**
     * [$fields description]
     *
     * @var [type]
     */
    protected $fields = array();

    /**
     * [$cache description]
     *
     * @var [type]
     */
    protected static $cache = array();

    /**
     * [$eagerLoad description]
     *
     * @var [type]
     */
    protected $eagerLoad = [];

    /**
     * [$passthru description]
     *
     * @var [type]
     */
    protected $passthru = array(
        'select',
        'insert',
        'insertIgnore',
        'replace',
        'update',
        'updateOrInsert',
        'delete',
        'count',
        'query',
        'addTablePrefix'
    );


    /**
     * [__construct description]
     *
     * @param   Model  $model       [$model description]
     * @param   [type] $connection  [$connection description]
     *
     * @return  [type]              [return description]
     */
    public function __construct(Model $model, $connection = null)
    {
        // Setup the Connection name.
        $this->connection = ($connection !== null) ? $connection : $model->getConnection();

        // Setup the Connection instance.
        $this->db = Database::getConnection($this->connection);

        // Setup the parent Model.
        $this->model = $model;

        // Finally, we initialize the Builder instance.
        $this->initialize();
    }

    /**
     * [initialize description]
     *
     * @return  [type]  [return description]
     */
    protected function initialize()
    {
        // Prepare the Table and Primary Key information from the Model.
        $this->table = $this->model->getTable();

        $this->primaryKey = $this->model->getKeyName();

        // Setup the inner Query Builder instance.
        $this->query = $this->newBaseQuery();

        // If the Fields are specified directly into Model, just use them and quit.
        $fields = $this->model->getFields();

        if(! empty($fields)) {
            $this->fields = $fields;

            return;
        }

        // Prepare the Cache token.
        $token = $this->connection .'_' .$this->table;

        // Check if the fields are already cached by a previous Builder instance.
        if($this->hasCached($token)) {
            $this->fields = $this->getCache($token);
        }
        // Get the Fields directly from the database connection, then cache them.
        else {
            $table = $this->query->addTablePrefix($this->table, false);

            $fields = $this->db->getTableFields($table);

            $this->fields = array_keys($fields);

            $this->setCache($token, $this->fields);
        }
    }

    /**
     * [getTable description]
     *
     * @return  [type]  [return description]
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * [table description]
     *
     * @return  [type]  [return description]
     */
    public function table()
    {
        return $this->query->addTablePrefix($this->table);
    }

    /**
     * [getFields description]
     *
     * @return  [type]  [return description]
     */
    public function getFields()
    {
        return $this->fields;
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
     * [getLink description]
     *
     * @return  [type]  [return description]
     */
    public function getLink()
    {
        return $this->db;
    }

    /**
     * [hasCached description]
     *
     * @param   [type]  $token  [$token description]
     *
     * @return  [type]          [return description]
     */
    public static function hasCached($token)
    {
        return isset(self::$cache[$token]);
    }

    /**
     * [setCache description]
     *
     * @param   [type]  $token  [$token description]
     * @param   [type]  $value  [$value description]
     *
     * @return  [type]          [return description]
     */
    public static function setCache($token, $value)
    {
        self::$cache[$token] = $value;
    }

    /**
     * [getCache description]
     *
     * @param   [type]  $token  [$token description]
     *
     * @return  [type]          [return description]
     */
    public static function getCache($token)
    {
        return isset(self::$cache[$token]) ? self::$cache[$token] : null;
    }

    /**
     * [newBaseQuery description]
     *
     * @return  [type]  [return description]
     */
    public function newBaseQuery()
    {
        $query = $this->db->getQueryBuilder();

        return $query->table($this->table)->asAssoc();
    }

    /**
     * [getBaseQuery description]
     *
     * @return  [type]  [return description]
     */
    public function getBaseQuery()
    {
        return $this->query;
    }

    /**
     * [__call description]
     *
     * @param   [type]  $method      [$method description]
     * @param   [type]  $parameters  [$parameters description]
     *
     * @return  [type]               [return description]
     */
    public function __call($method, $parameters)
    {
        $result = call_user_func_array(array($this->query, $method), $parameters);

        return in_array($method, $this->passthru) ? $result : $this;
    }

    /**
     * [find description]
     *
     * @param   [type]  $id         [$id description]
     * @param   [type]  $fieldName  [$fieldName description]
     *
     * @return  [type]              [return description]
     */
    public function find($id, $fieldName = null)
    {
        // We use a new Query to perform this operation.
        $query = $this->newBaseQuery();

        // Get the field name, using the primaryKey as default.
        $fieldName = ($fieldName !== null) ? $fieldName : $this->primaryKey;

        $result = $query->where($fieldName, $id)->first();

        if ($result !== null) {
            return $this->model->newFromBuilder($result)->load($this->eagerLoad);
        }

        return false;
    }

    /**
     * [findBy description]
     *
     * @return  [type]  [return description]
     */
    public function findBy()
    {
        $params = func_get_args();

        if (empty($params)) {
            throw new \UnexpectedValueException(__d('system', 'Invalid parameters'));
        }

        $query = call_user_func_array(array($this->query, 'where'), $params);

        $result = $query->first();

        if($result !== null) {
            return $this->model->newFromBuilder($result)->load($this->eagerLoad);
        }

        return false;
    }

    /**
     * [findMany description]
     *
     * @param   array  $values  [$values description]
     *
     * @return  [type]          [return description]
     */
    public function findMany(array $values)
    {
        if (empty($values)) {
            throw new \UnexpectedValueException(__d('system', 'Invalid parameters'));
        }

        $query = $this->newBaseQuery();

        $data = $query->findMany($this->primaryKey, $values);

        if($data === false) {
            return false;
        }

        // Prepare and return an instances array.
        $result = array();

        foreach($data as $row) {
            $result[] = $this->model->newFromBuilder($row)->load($this->eagerLoad);
        }

        return $result;
    }

    /**
     * [findAll description]
     *
     * @return  [type]  [return description]
     */
    public function findAll()
    {
        // Prepare the WHERE parameters.
        $params = func_get_args();

        if (! empty($params)) {
            $query = call_user_func_array(array($this->query, 'where'), $params);
        } else {
            $query = $this->query;
        }

        $data = $query->get();

        if($data === false) {
            return false;
        }

        // Prepare and return an instances array.
        $result = array();

        foreach($data as $row) {
            $result[] = $this->model->newFromBuilder($row)->load($this->eagerLoad);
        }

        return $result;
    }

    /**
     * [first description]
     *
     * @return  [type]  [return description]
     */
    public function first()
    {
        $data = $this->query->first();

        if($data !== null) {
            return $this->model->newFromBuilder($data)->load($this->eagerLoad);
        }

        return false;
    }

    /**
     * [updateBy description]
     *
     * @return  [type]  [return description]
     */
    public function updateBy()
    {
        $params = func_get_args();

        $data = array_pop($params);

        if (empty($params) || empty($data)) {
            throw new \UnexpectedValueException(__d('system', 'Invalid parameters'));
        }

        $query = call_user_func_array(array($this->query, 'where'), $params);

        return $query->update($data);
    }

    /**
     * [deleteBy description]
     *
     * @return  [type]  [return description]
     */
    public function deleteBy()
    {
        $params = func_get_args();

        if (empty($params)) {
            throw new \UnexpectedValueException(__d('system', 'Invalid parameters'));
        }

        $query = call_user_func_array(array($this->query, 'where'), $params);

        return $query->delete();
    }

    /**
     * [countBy description]
     *
     * @return  [type]  [return description]
     */
    public function countBy()
    {
        $params = func_get_args();

        if (empty($params)) {
            throw new \UnexpectedValueException(__d('system', 'Invalid parameters'));
        }

        // We use a new Query to perform this operation.
        $query = $this->newBaseQuery();

        call_user_func_array(array($query, 'where'), $params);

        return $query->count();
    }

    /**
     * [countAll description]
     *
     * @return  [type]  [return description]
     */
    public function countAll()
    {
        // We use a new Query to perform this operation.
        $query = $this->newBaseQuery();

        return $query->count();
    }

    /**
     * [isUnique description]
     *
     * @param   [type]  $field   [$field description]
     * @param   [type]  $value   [$value description]
     * @param   [type]  $ignore  [$ignore description]
     *
     * @return  [type]           [return description]
     */
    public function isUnique($field, $value, $ignore = null)
    {
        // We use a new Query to perform this operation.
        $query = $this->newBaseQuery();

        $query->where($field, $value);

        if ($ignore !== null) {
            $query->where($this->primaryKey, '!=', $ignore);
        }

        $result = $query->count();

        if($result == 0) {
            return true;
        }

        return false;
    }

    /**
     * [with description]
     *
     * @param   [type]  $relations  [$relations description]
     *
     * @return  [type]              [return description]
     */
    public function with($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        $this->eagerLoad = array_merge($this->eagerLoad, $relations);

        return $this;
    }

}
