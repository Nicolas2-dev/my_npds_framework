<?php

namespace Npds\Database\Driver;

use Npds\Database\Connection;
use Npds\Database\Manager;
use Npds\Cache\Manager as CacheManager;

/**
 * Undocumented class
 */
class PgSQL extends Connection
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
            $config['port'] = 5432;
        }

        // Prepare the options.
        $options = isset($config['options']) ? $config['options'] : array();

        // Prepare the PDO's DSN
        $dsn = "pgsql:host=" .$config['host'] .";port=" .$config['port'] .";dbname=" .$config['dbname'];

        // Execute the Parent Constructor.
        parent::__construct($dsn, $config, $options);

        // Post processing.
        if (isset($config['charset'])) {
            $this->prepare("SET NAMES '{$config['charset']}'")->execute();
        }

        if (isset($config['schema'])) {
            $this->prepare("SET search_path TO '{$config['schema']}'")->execute();
        }
    }

    /**
     * [getDriverName description]
     *
     * @return  [type]  [return description]
     */
    public function getDriverName()
    {
        return __d('system', 'PostgreSQL Driver');
    }

    /**
     * [getDriverCode description]
     *
     * @return  [type]  [return description]
     */
    public function getDriverCode()
    {
        return Manager::DRIVER_PGSQL;
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
     * @param   [type]  $pgsqlType  [$pgsqlType description]
     *
     * @return  [type]              [return description]
     */
    private static function getTableFieldType($pgsqlType)
    {
        if (preg_match("/^([^(]+)/", $pgsqlType, $match)) {
            switch (strtolower($match[1])) {
                case 'smallint':
                case 'integer':
                case 'bigint':
                case 'real':
                case 'double':
                case 'decimal':
                case 'numeric':
                case 'serial':
                case 'bigserial':
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
        $isNullable = strtoupper($data['is_nullable']);

        return array(
            'type' => self::getTableFieldType($data['data_type']),
            'null' => ($isNullable == 'YES') ? true : false
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
        $token = 'pgsql_table_fields_' .md5($table);

        // Setup the Cache instance.
        $cache = CacheManager::getCache();

        // Get the Table Fields, using the Framework Caching.
        $fields = $cache->get($token);

        if($fields === null) {
            $fields = array();

            // Find all Column names
            $sql = "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name ='$table';";

            // Get the current Time.
            $time = microtime(true);

            $result = $this->rawQuery($sql, 'assoc', false);

            $cid = 0;

            if ($result !== false) {
                foreach ($result as $row) {
                    $field = $row['column_name'];

                    unset($row['column_name']);

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
