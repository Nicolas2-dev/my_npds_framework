<?php

namespace Npds\Support;

use Npds\Database\Manager as Database;
use Npds\Database\Connection;
use Npds\Config\config;

/**
 * Undocumented class
 */
class Profiler
{

    /**
     * Undocumented function
     *
     * @return void
     */
    public static function report()
    {
        $options = Config::get('profiler');

        // Calculate the variables.
        $exectime = microtime(true) - FRAMEWORK_STARTING_MICROTIME;

        $elapsed_time = sprintf("%01.4f", $exectime);

        $memory_usage = Number::humanSize(memory_get_usage());

        if ($options['with_database'] == true) {
            $connection = Database::getConnection();

            $total_queries = $connection->getTotalQueries();

            $queries_str = ($total_queries == 1) ? __d('system', 'query') : __d('system', 'queries');
        } else {
            $total_queries = 0;

            $queries_str = __d('system', 'queries');
        }

        $estimated_users = sprintf("%0d", intval(25 / $exectime));

        //
        $retval = __d('system', 'Elapsed Time: <b>{0}</b> sec | Memory Usage: <b>{1}</b> | SQL: <b>{2}</b> {3} | UMAX: <b>{4}</b>', $elapsed_time, $memory_usage, $total_queries, $queries_str, $estimated_users);

        return $retval;
    }
    
}
