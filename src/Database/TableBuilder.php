<?php

namespace Npds\Database;

use Npds\Database\Manager as Database;
use \PDO;

/**
 * Table builder class for Npds Framework.
 * This class' purpose is to generate SQL code and execute query
 * to create MySQL table.
 *
 * For 'CREATE TABLE' syntax reference visit: http://dev.mysql.com/doc/refman/5.1/en/create-table.html [1]
 *
 * Example of usage:
 *
 * // After namespace: use \Npds\Database\TableBuilder;
 *
 * // Model or Controller method
 * $tableBuilder = new TableBuilder;
 *
 * $tableBuilder->addField('name', 'string', false);
 * $tableBuilder->addField('description', 'description', false);
 * $tableBuilder->addField('date', 'TIMESTAMP', false, tableBuilder::CURRENT_TIMESTAMP);
 * $tableBuilder->addField('online', 'TINYINT(1)', false);
 *
 * $tableBuilder->setDefault('online', 0);
 * $tableBuilder->setName('comments');
 * $tableBuilder->setNotExists(true);
 *
 * $tableBuilder->create();
 *
 * @author volter9
 * @copyright volter9 ( c ) 2014
 */
class TableBuilder
{
    /**
     * 
     */
    const AUTO_INCREMENT = 1;

    /**
     * 
     */
    const CURRENT_TIMESTAMP = 2;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    protected $db;

    /**
     * Undocumented variable
     *
     * @var string
     */
    private $sql = '';

    /**
     * Undocumented variable
     *
     * @var string
     */
    private $name = '';

    /**
     * Undocumented variable
     *
     * @var array
     */
    private $fields = array();

    /**
     * Undocumented variable
     *
     * @var string
     */
    private $pk = '';

    /**
     * Undocumented variable
     *
     * @var boolean
     */
    private $notExists = false;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private static $typeAliases = array (
        'int'         => 'INT(11)',
        'string'      => 'VARCHAR(255)',
        'description' => 'TINYTEXT'
    );


    /**
     * Undocumented function
     *
     * @param [type] $aliasName
     * @param [type] $aliasType
     * @return void
     */
    public static function setAlias($aliasName, $aliasType)
    {
        self::$typeAliases[$aliasName] = $aliasType;
    }

    /**
     * Undocumented function
     *
     * @param \PDO $db
     * @param boolean $id
     */
    public function __construct(\PDO $db = null, $id = true)
    {
        // If database is not given, create new database instance.
        // database is in the same namespace, we don't need to specify namespace
        $this->db = ($db === null) ? Database::getConnection() : $db;

        if ($id === true) {
            $this->addField('id', 'INT(11)', false, self::AUTO_INCREMENT);
            $this->setPK('id');
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $constant
     * @return void
     */
    private function getOptions($constant)
    {
        if (is_array($constant)) {
            $str = '';

            foreach ($constant as $value) {
                $str .= $this->getOptions($value);
            }

            return trim($str);
        }

        switch ($constant) {
            case self::AUTO_INCREMENT:
                return 'AUTO_INCREMENT';

            default:
                return '';
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $field
     * @param [type] $type
     * @param boolean $null
     * @param integer $options
     * @return void
     */
    public function addField($field, $type, $null = false, $options = 0)
    {
        // Check for alias
        if (isset(self::$typeAliases[$type])) {
            $type = self::$typeAliases[$type];
        }

        $this->fields[$field] = array (
            'type'    => $type,
            'null'    => $null,
            'options' => $options
        );

        if ($options === self::CURRENT_TIMESTAMP ||
            is_array($options) &&
            in_array(self::CURRENT_TIMESTAMP, $options)) {
            $this->fields[$field]['default'] = 'CURRENT_TIMESTAMP';
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $boolean
     * @return void
     */
    public function setNotExists($boolean)
    {
        $this->notExists = $boolean;
    }

    /**
     * Undocumented function
     *
     * @param [type] $field
     * @return void
     */
    public function setPK($field)
    {
        if (!isset($this->fields[$field])) {
            return false;
        }

        $this->pk = $field;

        return true;
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @return void
     */
    public function setName($name)
    {
        if (is_string($name) && $name !== '') {
            $this->name = $name;
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $field
     * @param [type] $value
     * @return void
     */
    public function setDefault($field, $value)
    {
        if (is_string($value)) {
            $value = "'$value'";
        }

        $this->fields[$field]['default'] = $value;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function generateSQL()
    {
        $sql = 'CREATE TABLE ';

        if ($this->notExists) {
            $sql = $sql . 'IF NOT EXISTS ';
        }

        $sql .= "{$this->name} (";

        // Handling fields
        foreach ($this->fields as $name => $field) {
            $sql .= "`$name` {$field['type']} " . ($field['null'] === false ? 'NOT' : '') . " null ";

            if (isset($field['default'])) {
                $sql .= "DEFAULT {$field['default']} ";
            }

            $sql .= $this->getOptions($field['options']) . ', ';
        }

        if ($this->pk !== '') {
            $sql .= "CONSTRAINT pk_{$this->pk} PRIMARY KEY (`{$this->pk}`)";
        }

        // Removing additional commas
        $sql = rtrim($sql, ', ') . ')';

        $this->sql = $sql;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getSQL()
    {
        if (!$this->sql) {
            $this->generateSQL();
        }

        return $this->sql;
    }

    /**
     * Undocumented function
     *
     * @param boolean $reset
     * @return void
     */
    public function create($reset = true)
    {
        if (!$this->sql) {
            $this->generateSQL();
        }

        $result = $this->db->exec($this->sql);

        if ($reset) {
            $this->reset();
        }

        return $result !== false;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function reset()
    {
        $this->sql = '';
        $this->name = '';
        $this->pk = '';
        $this->notExists = false;

        $this->fields = array();
    }
    
}
