<?php

namespace Npds\Database\Driver;

use Npds\Database\Connection;
use Npds\Database\Manager;
use Npds\Cache\Manager as CacheManager;

/**
 * Undocumented class
 */
class MySQL extends Connection
{

    /**
     * [__construct description]
     *
     * @param   array  $config  [$config description]
     *
     * @return  [type]          [return description]
     */
    public function __construct(array $config)
    {
        // Check for valid Config.
        if (! is_array($config)) {
            throw new \UnexpectedValueException('Parameter should be an Array');
        }

        // Default port if no port is provided.
        if (! isset($config['port'])) {
            $config['port'] = 3306;
        }

        // Some Database Servers go crazy when a charset parameter is added, then we should make it optional.
        if (! isset($config['charset'])) {
            $charsetStr = "";
        } else {
            $charsetStr = ($config['charset'] == 'auto') ? "" : ";charset=" . $config['charset'];
        }

        // Prepare the options.
        $options = isset($config['options']) ? $config['options'] : array();

        // Prepare the PDO's DSN
        $dsn = "mysql:host=" .$config['host'] .";port=" .$config['port'] .";dbname=" .$config['dbname'] .$charsetStr;

        parent::__construct($dsn, $config, $options);
    }

    /**
     * [getDriverName description]
     *
     * @return  [type]  [return description]
     */
    public function getDriverName()
    {
        return __d('system', 'MySQL Driver');
    }

    /**
     * [getDriverCode description]
     *
     * @return  [type]  [return description]
     */
    public function getDriverCode()
    {
        return Manager::DRIVER_MYSQL;
    }

    /**
     * [truncate description]
     *
     * @param   [type]  $table  [$table description]
     *
     * @return  [type]          [return description]
     */
    public function truncate($table)
    {
        $sql = "TRUNCATE TABLE $table";

        // Get the current Time.
        $time = microtime(true);

        $result = $this->exec($sql);

        $this->logQuery($sql, $time);

        return $result;
    }

    /**
     * [getTableFieldType description]
     *
     * @param   [type]  $mysqlType  [$mysqlType description]
     *
     * @return  [type]              [return description]
     */
    private static function getTableFieldType($mysqlType)
    {
        if (preg_match("/^([^(]+)/", $mysqlType, $match)) {
            switch (strtolower($match[1])) {
                case 'tinyint':
                case 'smallint':
                case 'mediumint':
                case 'int':
                case 'bigint':
                case 'float':
                case 'double':
                case 'decimal':
                    return 'int';

                default:
                    return 'string';
            }
        }

        return 'string';
    }

    /**
     * [getTableFieldData description]
     *
     * @param   [type]  $data  [$data description]
     *
     * @return  [type]         [return description]
     */
    private function getTableFieldData($data)
    {
        return array(
            'type' => self::getTableFieldType($data['Type']),
            'null' => ($data['Null'] == 'YES') ? true : false
        );
    }

    /**
     * [getTableFields description]
     *
     * @param   [type]  $table  [$table description]
     *
     * @return  [type]          [return description]
     */
    public function getTableFields($table)
    {
        $columns = array();

        if (empty($table)) {
            throw new \UnexpectedValueException('Parameter should be not empty');
        }

        if(isset(Connection::$tables[$table])) {
            $fields = Connection::$tables[$table];

            foreach($fields as $field => $row) {
                $columns[$field] = $this->getTableFieldData($row);
            }

            return $columns;
        }

        // Prepare the Cache Token.
        $token = 'mysql_table_fields_' .md5($table);

        // Setup the Cache instance.
        $cache = CacheManager::getCache();

        // Get the Table Fields, using the Framework Caching.
        $fields = $cache->get($token);

        if($fields === null) {
            $fields = array();

            // Find all Column names
            $sql = "SHOW COLUMNS FROM $table";

            // Get the current Time.
            $time = microtime(true);

            $result = $this->rawQuery($sql, 'assoc', false);

            $cid = 0;

            if ($result !== false) {
                foreach ($result as $row) {
                    $field = $row['Field'];

                    unset($row['Field']);

                    $row['CID'] = $cid;

                    $fields[$field] = $row;

                    // Prepare the column entry
                    $columns[$field] = $this->getTableFieldData($row);

                    $cid++;
                }
            }

            $this->logQuery($sql, $time);

            // Write to Cache 300 seconds = 5 minutes
            $cache->set($token, $fields, 300);
        } else {
            foreach($fields as $field => $row) {
                $columns[$field] = $this->getTableFieldData($row);
            }
        }

        // Write to local static Cache
        Connection::$tables[$table] = $fields;

        return $columns;
    }

}
