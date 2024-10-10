<?php

namespace Npds\Database;

use Npds\Database\Statement;
use Npds\Database\Query\Builder as QueryBuilder;
use Npds\Database\EventHandler;
use Npds\Config\Config;

use \Closure;
use \PDO;

/**
 * Undocumented class
 */
abstract class Connection extends PDO
{

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    protected $lastSqlQuery = null;

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $returnType = 'assoc';

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    protected $config;

    /**
     * Undocumented variable
     *
     * @var integer
     */
    protected $queryCount = 0;

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected static $tables = array();

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected $queries = array();

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    protected $eventHandler;


    /**
     * Undocumented function
     *
     * @param [type] $dsn
     * @param array $config
     * @param array $options
     */
    public function __construct($dsn, array $config, array $options = array())
    {
        // Check for valid Config.
        if (! is_array($config) || ! is_array($options)) {
            throw new \UnexpectedValueException(__d('system', 'Config and Options parameters should be Arrays'));
        }

        // Will set the default method when provided in the config.
        if (isset($config['return_type'])) {
            $this->returnType = $config['return_type'];
        }

        // Prepare the FetchMethod and check the returnType
        list($fetchMethod) = self::getFetchMethod($this->returnType);

        // Store the config in class variable.
        $this->config = $config;

        // Prepare the parameters.
        $username = isset($config['user']) ? $config['user'] : '';
        $password = isset($config['password']) ? $config['password'] : '';

        // Call the PDO constructor.
        parent::__construct($dsn, $username, $password, $options);

        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //$this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $fetchMethod);

        // Create the Event Handler instance.
        $this->eventHandler = new EventHandler();
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    abstract public function getDriverName();

    /**
     * Undocumented function
     *
     * @return void
     */
    abstract public function getDriverCode();

    /**
     * Undocumented function
     *
     * @param [type] $type
     * @return void
     */
    public function returnType($type = null)
    {
        if ($type === null) {
            return $this->returnType;
        }

        $this->returnType = $type;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getOptions()
    {
        return $this->config;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getLink()
    {
        return $this;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getQueryBuilder()
    {
        return new QueryBuilder($this);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getEventHandler()
    {
        return $this->eventHandler;
    }

    /**
     * Undocumented function
     *
     * @param [type] $returnType
     * @return void
     */
    public static function getFetchMethod($returnType)
    {
        // Prepare the parameters.
        $fetchClass = null;

        if ($returnType == 'assoc') {
            $fetchMethod = PDO::FETCH_ASSOC;
        } else if ($returnType == 'array') {
            $fetchMethod = PDO::FETCH_NUM;
        } else if ($returnType == 'object') {
            $fetchMethod = PDO::FETCH_OBJ;
        } else {
            $fetchMethod = PDO::FETCH_CLASS;

            // Check and setup the className.
            $classPath = str_replace('\\', '/', ltrim($returnType, '\\'));

            if (! preg_match('#^App(?:/Modules/.+)?/Models/Entities/(.*)$#i', $classPath)) {
                throw new \Exception(__d('system', 'No valid Entity Name is given: {0}', $returnType));
            }

            if (! class_exists($returnType)) {
                throw new \Exception(__d('system', 'No valid Entity Class is given: {0}', $returnType));
            }

            $fetchClass = $returnType;
        }

        return array($fetchMethod, $fetchClass);
    }

    /**
     * Undocumented function
     *
     * @param string $query
     * @param integer|null $method
     * @param mixed ...$fetchModeArgs
     * @return void
     */
    public function query(string $query, ?int $method = null, mixed ...$fetchModeArgs)
    {
        $start = microtime(true);

        if($method !== null) {
            $result = parent::query($query, $method);
        } else {
            $result = parent::query($query);
        }

        $this->logQuery($query, $start);

        return $result;
    }

    /**
     * Undocumented function
     *
     * @param [type] $query
     * @return void
     */
    public function exec($query): int|false
    {
        $start = microtime(true);

        // Execute the Query.
        $result = parent::exec($query);

        $this->logQuery($query, $start);

        return $result;
    }

    /**
     * Undocumented function
     *
     * @param string $sql
     * @param array $options
     * @return void
     */
    public function prepare(string $sql, array $options = []): Statement|false
    {
        if(is_array($options)) {
            $statement = parent::prepare($sql, $options);
        } else {
            $statement = parent::prepare($sql);
        }

        return new Statement($statement, $this);
    }

    /**
     * Undocumented function
     *
     * @param [type] $sql
     * @param boolean $fetch
     * @return void
     */
    public function raw($sql, $fetch = false)
    {
        // Get the current Time.
        $start = microtime(true);

        // We can't fetch class here to stay conform the interface, make it OBJ for this simple query.
        if($this->returnType == 'assoc') {
            $method = PDO::FETCH_ASSOC;
        } else if($this->returnType == 'array') {
            $method = PDO::FETCH_NUM;
        } else {
            $method = PDO::FETCH_OBJ;
        }

        if (! $fetch) {
            $result = $this->exec($sql);
        } else {
            $statement = parent::query($sql, $method);

            $result = $statement->fetchAll();
        }

        $this->logQuery($sql, $start);

        return $result;
    }

    /**
     * Undocumented function
     *
     * @param [type] $sql
     * @param [type] $returnType
     * @param boolean $useLogging
     * @return void
     */
    public function rawQuery($sql, $returnType = null, $useLogging = true)
    {
        // Get the current Time.
        $start = microtime(true);

        // What return type? Use default if no return type is given in the call.
        $returnType = $returnType ? $returnType : $this->returnType;

        // We can't fetch class here to stay conform the interface, make it OBJ for this simple query.
        if($returnType == 'assoc') {
            $method = PDO::FETCH_ASSOC;
        } else if($returnType == 'array') {
            $method = PDO::FETCH_NUM;
        } else {
            $method = PDO::FETCH_OBJ;
        }

        // We don't want to map in memory an entire Billion Records Table, so we return right on a Statement.
        $result = parent::query($sql, $method);

        if($useLogging) {
            $this->logQuery($sql, $start);
        } else {
            $this->queryCount++;

            $this->lastSqlQuery = $sql;
        }

        return $result;
    }

    /**
     * Undocumented function
     *
     * @param [type] $sql
     * @param array $params
     * @param array $paramTypes
     * @return void
     */
    public function rawPrepare($sql, $params = array(), array $paramTypes = array())
    {
        // Prepare and get statement from PDO.
        $stmt = $this->prepare($sql);

        if($stmt === false) {
            return false;
        }

        // Bind the parameters.
        if(! empty($paramTypes)) {
            $this->bindTypedValues($stmt, $params, $paramTypes);
        } else {
            foreach ($params as $key => $value) {
                $bindType = (is_int($value) || is_bool($value)) ? PDO::PARAM_INT : PDO::PARAM_STR;

                $stmt->bindValue(is_integer($key) ? $key + 1 : $key, $value, $bindType);
            }
        }

        return $stmt;
    }

    /**
     * Undocumented function
     *
     * @param [type] $statement
     * @param array $params
     * @param array $paramTypes
     * @param boolean $fetchAll
     * @return void
     */
    public function fetchAssoc($statement, array $params = array(), array $paramTypes = array(), $fetchAll = false)
    {
        return $this->select($statement, $params, $paramTypes, 'assoc', $fetchAll);
    }

    /**
     * Undocumented function
     *
     * @param [type] $statement
     * @param array $params
     * @param array $paramTypes
     * @param boolean $fetchAll
     * @return void
     */
    public function fetchArray($statement, array $params = array(), array $paramTypes = array(), $fetchAll = false)
    {
        return $this->select($statement, $params, $paramTypes, 'array', $fetchAll);
    }

    /**
     * Undocumented function
     *
     * @param [type] $statement
     * @param array $params
     * @param array $paramTypes
     * @param boolean $fetchAll
     * @return void
     */
    public function fetchObject($statement, array $params = array(), array $paramTypes = array(), $fetchAll = false)
    {
        return $this->select($statement, $params, $paramTypes, 'object', $fetchAll);
    }

    /**
     * Undocumented function
     *
     * @param [type] $statement
     * @param array $params
     * @param array $paramTypes
     * @param [type] $returnType
     * @param boolean $fetchAll
     * @return void
     */
    public function fetchClass($statement, array $params = array(), array $paramTypes = array(), $returnType = null, $fetchAll = false)
    {
        if (($this->returnType != 'assoc') && ($this->returnType != 'array') && ($this->returnType != 'object')) {
            $returnType = ($returnType !== null) ? $returnType : $this->returnType;
        } else if ($returnType === null) {
            throw new \Exception(__d('system', 'No valid Entity Class is given'));
        }

        return $this->select($statement, $params, $paramTypes, $returnType, $fetchAll);
    }

    /**
     * Undocumented function
     *
     * @param [type] $sql
     * @param array $params
     * @param array $paramTypes
     * @param [type] $returnType
     * @return void
     */
    public function fetchAll($sql, array $params = array(), $paramTypes = array(), $returnType = null)
    {
        return $this->select($sql, $params, $paramTypes, $returnType, true);
    }

    /**
     * Undocumented function
     *
     * @param [type] $sql
     * @param array $params
     * @param array $paramTypes
     * @param [type] $returnType
     * @param boolean $fetchAll
     * @return void
     */
    public function select($sql, array $params = array(), array $paramTypes = array(), $returnType = null, $fetchAll = false)
    {
        // What return type? Use default if no return type is given in the call.
        $returnType = $returnType ? $returnType : $this->returnType;

        // Prepare the FetchMethod and check the returnType
        list($fetchMethod, $className) = self::getFetchMethod($returnType);

        // Execute the Query.
        $stmt = $this->executeQuery($sql, $params, $paramTypes);

        if($stmt === false) {
            return false;
        }

        // Fetch the data.
        $result = false;

        if ($fetchMethod === PDO::FETCH_CLASS) {
            $stmt->setFetchMode($fetchMethod, $className);
        } else {
            $stmt->setFetchMode($fetchMethod);
        }

        if ($fetchAll) {
            // Fetch and return all records.
            return $stmt->fetchAll();
        }

        // Fetch and return one record.
        return $stmt->fetch();
    }

    /**
     * Undocumented function
     *
     * @param [type] $sql
     * @param array $params
     * @param array $paramTypes
     * @param [type] $returnType
     * @return void
     */
    public function selectOne($sql, array $params = array(), array $paramTypes = array(), $returnType = null)
    {
        return $this->select($sql, $params, $paramTypes, $returnType);
    }

    /**
     * Undocumented function
     *
     * @param [type] $sql
     * @param array $params
     * @param array $paramTypes
     * @param [type] $returnType
     * @return void
     */
    public function selectAll($sql, array $params = array(), array $paramTypes = array(), $returnType = null)
    {
        return $this->select($sql, $params, $paramTypes, $returnType, true);
    }

    /**
     * Undocumented function
     *
     * @param [type] $table
     * @param array $data
     * @param array $paramTypes
     * @return void
     */
    public function insert($table, array $data, array $paramTypes = array())
    {
        // Prepare the SQL statement.
        $sql = 'INSERT INTO ' .$table .' (' . implode(', ', array_keys($data)) .') VALUES (' .implode(', ', array_fill(0, count($data), '?')) .')';

        // Execute the Update and capture the result.
        $result = $this->executeUpdate(
            $sql,
            array_values($data),
            is_string(key($paramTypes)) ? $this->extractTypeValues($data, $paramTypes) : $paramTypes
        );

        // If no error, return the connection's last inserted ID
        if($result !== false) {
            return $this->lastInsertId();
        }

        return false;
    }

    /**
     * Undocumented function
     *
     * @param [type] $table
     * @param array $data
     * @param array $where
     * @param array $paramTypes
     * @return void
     */
    public function update($table, array $data, array $where, array $paramTypes = array())
    {
        $set = array();

        foreach ($data as $columnName => $value) {
            $set[] = $columnName . ' = ?';
        }

        if (is_string(key($paramTypes))) {
            $paramTypes = $this->extractTypeValues(array_merge($data, $where), $paramTypes);
        }

        $params = array_merge(array_values($data), array_values($where));

        $sql  = 'UPDATE ' .$table .' SET ' .implode(', ', $set) .' WHERE ' .implode(' = ? AND ', array_keys($where)) .' = ?';

        // Execute the Update and return the result.
        return $this->executeUpdate($sql, $params, $paramTypes);
    }

    /**
     * Undocumented function
     *
     * @param [type] $table
     * @param array $where
     * @param array $paramTypes
     * @return void
     */
    public function delete($table, array $where, array $paramTypes = array())
    {
        $criteria = array();

        foreach (array_keys($where) as $columnName) {
            $criteria[] = $columnName . ' = ?';
        }

        // Prepare the SQL statement.
        $sql = 'DELETE FROM ' .$table .' WHERE ' .implode(' AND ', $criteria);

        // Execute the Update and return the result.
        return $this->executeUpdate(
            $sql,
            array_values($where),
            is_string(key($paramTypes)) ? $this->extractTypeValues($where, $paramTypes) : $paramTypes
        );
    }

    /**
     * Undocumented function
     *
     * @param [type] $query
     * @param array $params
     * @param array $paramTypes
     * @return void
     */
    public function executeQuery($query, array $params = array(), array $paramTypes = array())
    {
        if(empty($params)) {
            // No parameters given, so we execute a bare 'query'.
            return $this->query($query);
        }

        // Prepare the SQL Query.
        $stmt = $this->prepare($query);

        // Execute the Query with parameters binding.
        if(! empty($paramTypes)) {
            // Bind the parameters.
            $this->bindTypedValues($stmt, $params, $paramTypes);

            // Execute and return false if failure.
            $result = $stmt->execute();
        } else {
            $result = $stmt->execute($params);
        }

        if($result !== false) {
            // Return the Statement when succeeded.
            return $stmt;
        }

        return false;
    }

    /**
     * Undocumented function
     *
     * @param [type] $query
     * @param array $params
     * @param array $paramTypes
     * @return void
     */
    public function executeUpdate($query, array $params = array(), array $paramTypes = array())
    {
        if(empty($params)) {
            // No parameters given, so we execute a bare 'exec'.
            return $this->exec($query);
        }

        // Prepare the SQL Query.
        $stmt = $this->prepare($query);

        // Execute the Query with parameters binding.
        if(! empty($paramTypes)) {
            // Bind the parameters.
            $this->bindTypedValues($stmt, $params, $paramTypes);

            // Execute and return false if failure.
            $result = $stmt->execute();
        } else {
            $result = $stmt->execute($params);
        }

        if ($result !== false) {
            // Return rowcount when succeeded.
            return $stmt->rowCount();
        }

        return false;
    }

    /**
     * Undocumented function
     *
     * @param [type] $stmt
     * @param array $params
     * @param array $paramTypes
     * @return void
     */
    private function bindTypedValues($stmt, array $params, array $paramTypes = array())
    {
        if (empty($params)) {
            return;
        }

        foreach ($params as $key => $value) {
            $bindKey = is_integer($key) ? $key + 1 : $key;

            if (isset($paramTypes[$key])) {
                $bindType = $paramTypes[$key];
            } else {
                $bindType = (is_int($value) || is_bool($value)) ? PDO::PARAM_INT : PDO::PARAM_STR;
            }

            $stmt->bindValue($bindKey, $value, $bindType);
        }
    }

    /**
     * Undocumented function
     *
     * @param array $params
     * @param array $paramTypes
     * @return void
     */
    private function extractTypeValues(array $params, array $paramTypes)
    {
        $result = array();

        foreach ($params as $key => $_) {
            $result[] = isset($paramTypes[$key]) ? $paramTypes[$key] : PDO::PARAM_STR;
        }

        return $result;
    }

    /**
     * Undocumented function
     *
     * @param [type] $string
     * @param [type] $paramType
     * @return void
     */
    public function escape($string, $paramType = PDO::PARAM_STR)
    {
        return parent::quote($string, $paramType);
    }

    /**
     * Undocumented function
     *
     * @param [type] $table
     * @return void
     */
    abstract public function truncate($table);

    /**
     * Undocumented function
     *
     * @param Closure $closure
     * @return void
     */
    public function transactional(Closure $closure)
    {
        $this->beginTransaction();

        try {
            $closure($this);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();

            throw $e;
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $table
     * @return void
     */
    abstract public function getTableFields($table);

    /**
     * Undocumented function
     *
     * @param [type] $table
     * @return void
     */
    public function getTableBindTypes($table)
    {
        $fields = $this->getTableFields($table);

        // Prepare the column types list.
        $result = array();

        foreach($fields as $fieldName => $fieldInfo) {
            $result[$fieldName] = ($fieldInfo['type'] == 'int') ? PDO::PARAM_INT : PDO::PARAM_STR;
        }

        return $result;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getTotalQueries()
    {
        return $this->queryCount;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function countIncomingQuery()
    {
        $this->queryCount++;
    }

    /**
     * Undocumented function
     *
     * @param [type] $sql
     * @param integer $start
     * @param array $params
     * @return void
     */
    function logQuery($sql, $start = 0, array $params = array())
    {
        $options = Config::get('profiler');

        // Count the current Query.
        $this->queryCount++;

        $this->lastSqlQuery = $sql;

        // Verify if the Forensics are enabled into Configuration.
        if ($options['use_forensics'] == true) {
            $start = ($start > 0) ? intval($start) : microtime(true);

            $time = microtime(true);

            //$time = ($time - $start) * 1000;
            $time = $time - $start;

            $query = array(
                'sql' => $sql,
                'params' => $params,
                'time' => $time
            );

            array_push($this->queries, $query);
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getExecutedQueries()
    {
        return $this->queries;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getLastQuery()
    {
        return $this->lastSqlQuery;
    }

}
