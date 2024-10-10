<?php


namespace Npds\Core;

use Npds\Database\Connection;
use Npds\Database\Manager as Database;

/**
 * Undocumented class
 */
abstract class Model
{
    
    /**
     * [$db description]
     *
     * @var [type]
     */
    protected $db;


    /**
     * [__construct description]
     *
     * @param   [type]  $connection  [$connection description]
     *
     * @return  [type]               [return description]
     */
    public function __construct($connection = null)
    {
        if ($connection instanceof Connection) {
            // Set the given Database Connection.
            $this->db = $connection;
        } else {
            $connection = ($connection !== null) ? $connection : 'default';

            // Setup the Database Connection.
            $this->db = Database::getConnection($connection);
        }
    }
    
}
