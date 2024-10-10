<?php

namespace Npds\Console;

use Npds\Database\Connection;
use Npds\Database\Manager as Database;
use Npds\Config\config;
use Npds\Console\Console;
use Npds\Console\PdoDebugger;

use \PDO;

/**
 * [Profiler description]
 */
class Profiler
{
    /**
     * [$db description]
     *
     * @var [type]
     */
    protected $db = null;

    /**
     * [$viewPath description]
     *
     * @var [type]
     */
    protected $viewPath;

    /**
     * [$startTime description]
     *
     * @var [type]
     */
    protected $startTime;

    /**
     * [$output description]
     *
     * @var [type]
     */
    public $output = array();


    /**
     * [__construct description]
     *
     * @param   [type]  $connection  [$connection description]
     *
     * @return  [type]               [return description]
     */
    public function __construct($connection = null)
    {
        $options = Config::get('profiler');

        if ($options['use_forensics'] != true) {
            return;
        }

        if($connection instanceof Connection) {
            $this->db = $connection;
        } else if($options['with_database'] == true) {
            $this->db = Database::getConnection();
        }

        // Setup the View path.
        $this->viewPath = realpath(__DIR__) .DS .'Views' .DS .'profiler.php';

        // Setup the Start Time.
        $this->startTime = FRAMEWORK_STARTING_MICROTIME;
    }

    /**
     * [process description]
     *
     * @param   [type] $fetch  [$fetch description]
     * @param   false          [ description]
     *
     * @return  [type]         [return description]
     */
    public static function process($fetch = false)
    {
        $options = Config::get('profiler');

        if ($options['use_forensics'] != true) {
            return null;
        }

        // The QuickProfiller was enabled into Configuration.
        $profiler = new self();

        return $profiler->display($fetch);
    }

    /**
     * [gatherConsoleData description]
     *
     * @return  [type]  [return description]
     */
    public function gatherConsoleData()
    {
        $logs = Console::getLogs();

        if(isset($logs['console'])) {
            foreach($logs['console'] as $key => $log) {
                if($log['type'] == 'log') {
                    $logs['console'][$key]['data'] = print_r($log['data'], true);
                }
                else if($log['type'] == 'memory') {
                    $logs['console'][$key]['data'] = $this->getReadableFileSize($log['data']);
                }
                else if($log['type'] == 'speed') {
                    $logs['console'][$key]['data'] = $this->getReadableTime(($log['data'] - $this->startTime) * 1000);
                }
            }
        }

        $this->output['logs'] = $logs;
    }

    /**
     * [gatherFileData description]
     *
     * @return  [type]  [return description]
     */
    public function gatherFileData()
    {
        $files = get_included_files();

        $fileList = array();

        $fileTotals = array(
            "count" => count($files),
            "size" => 0,
            "largest" => 0,
        );

        foreach($files as $key => $file) {
            $size = filesize($file);

            $fileList[] = array(
                'name' => str_replace(BASEPATH, '/', $file),
                'size' => $this->getReadableFileSize($size)
            );

            $fileTotals['size'] += $size;

            if($size > $fileTotals['largest']) $fileTotals['largest'] = $size;
        }

        $fileTotals['size'] = $this->getReadableFileSize($fileTotals['size']);
        $fileTotals['largest'] = $this->getReadableFileSize($fileTotals['largest']);

        $this->output['files'] = $fileList;
        $this->output['fileTotals'] = $fileTotals;
    }

    /**
     * [gatherMemoryData description]
     *
     * @return  [type]  [return description]
     */
    public function gatherMemoryData()
    {
        $memoryTotals = array();

        $memoryTotals['used'] = $this->getReadableFileSize(memory_get_peak_usage());

        $memoryTotals['total'] = ini_get("memory_limit");

        $this->output['memoryTotals'] = $memoryTotals;
    }

    /**
     * [gatherSQLQueryData description]
     *
     * @return  [type]  [return description]
     */
    public function gatherSQLQueryData()
    {
        $queryTotals = array();

        $queryTotals['count'] = 0;
        $queryTotals['time'] = 0;

        $queries = array();

        if($this->db !== null) {
            $queryTotals['count'] += $this->db->getTotalQueries();

            foreach($this->db->getExecutedQueries() as $key => $query) {
                if(isset($query['params']) && ! empty($query['params'])) {
                    $query['sql'] = PdoDebugger::show($query['sql'], $query['params']);
                }

                $query = $this->attemptToExplainQuery($query);

                $queryTotals['time'] += $query['time'];

                $query['time'] = $this->getReadableTime($query['time']);

                $queries[] = $query;
            }
        }

        $queryTotals['time'] = $this->getReadableTime($queryTotals['time']);

        $this->output['queries'] = $queries;
        $this->output['queryTotals'] = $queryTotals;
    }

    /**
     * [attemptToExplainQuery description]
     *
     * @param   [type]  $query  [$query description]
     *
     * @return  [type]          [return description]
     */
    function attemptToExplainQuery($query)
    {
        try {
            $statement = $this->db->query('EXPLAIN '.$query['sql']);

            if($statement !== false) {
                $query['explain'] = $statement->fetch(PDO::FETCH_ASSOC);
            }
        }
        catch(\Exception $e) {
            // Do nothing.
        }

        return $query;
    }

    /**
     * [gatherSpeedData description]
     *
     * @return  [type]  [return description]
     */
    public function gatherSpeedData()
    {
        $speedTotals = array();

        $speedTotals['total'] = $this->getReadableTime((microtime(true) - $this->startTime) * 1000);
        $speedTotals['allowed'] = ini_get("max_execution_time");

        $this->output['speedTotals'] = $speedTotals;
    }

    /**
     * [gatherFrameworkData description]
     *
     * @return  [type]  [return description]
     */
    public function gatherFrameworkData()
    {
        $instance = get_instance();

        //
        $output = array();

        // Controller variables
        $data = get_instance()->data();

        if (count($data) == 0) {
            $output['controller'] = __d('system', 'No Controller data exists');
        } else {
            $output['controller'] = array();

            ksort($data);

            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $output['controller']['&#36;'. $key] = '<pre>'. htmlspecialchars(stripslashes(print_r($value, TRUE))) .'</pre>';
                } else {
                    $output['controller']['&#36;'. $key] = htmlspecialchars(stripslashes($value));
                }
            }
        }

        // GET variables
        if (count($_GET) == 0) {
            $output['get'] = __d('system', 'No GET data exists');
        } else {
            $output['get'] = array();

            foreach ($_GET as $key => $value) {
                if ( ! is_numeric($key)) {
                    $key = "'".$key."'";
                }

                if (is_array($value)) {
                    $output['get']['&#36;_GET['. $key .']'] = '<pre>'. htmlspecialchars(stripslashes(print_r($value, TRUE))) .'</pre>';
                } else {
                    $output['get']['&#36;_GET['. $key .']'] = htmlspecialchars(stripslashes($value));
                }
            }
        }

        // POST variables
        if (count($_POST) == 0) {
            $output['post'] = __d('system', 'No POST data exists');
        } else {
            $output['post'] = array();

            foreach ($_POST as $key => $value) {
                if ( ! is_numeric($key)) {
                    $key = "'".$key."'";
                }

                if (is_array($value)) {
                    $output['post']['&#36;_POST['. $key .']'] = '<pre>'. htmlspecialchars(stripslashes(print_r($value, TRUE))) .'</pre>';
                } else {
                    $output['post']['&#36;_POST['. $key .']'] = htmlspecialchars(stripslashes($value));
                }
            }
        }

        // Server Headers
        $output['headers'] = array();

        $headers = array(
            'HTTP_ACCEPT',
            'HTTP_USER_AGENT',
            'HTTP_CONNECTION',
            'SERVER_PORT',
            'SERVER_NAME',
            'REMOTE_ADDR',
            'SERVER_SOFTWARE',
            'HTTP_ACCEPT_LANGUAGE',
            'SCRIPT_NAME',
            'REQUEST_METHOD',
            ' HTTP_HOST',
            'REMOTE_HOST',
            'CONTENT_TYPE',
            'SERVER_PROTOCOL',
            'QUERY_STRING',
            'HTTP_ACCEPT_ENCODING',
            'HTTP_X_FORWARDED_FOR'
        );

        foreach ($headers as $header) {
            $value = (isset($_SERVER[$header])) ? $_SERVER[$header] : '';

            $output['headers'][$header] = $value;
        }

        // Store the information.
        $this->output['variables'] = $output;
    }

    /**
     * [getReadableFileSize description]
     *
     * @param   [type]  $size    [$size description]
     * @param   [type]  $result  [$result description]
     *
     * @return  [type]           [return description]
     */
    public function getReadableFileSize($size, $result = null)
    {
        // Adapted from code at http://aidanlister.com/repos/v/function.size_readable.php
        $sizes = array('bytes', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

        if ($result === null) $result = '%01.2f %s';

        $lastSizeStr = end($sizes);

        foreach ($sizes as $sizeStr) {
            if ($size < 1024) break;

            if ($sizeStr != $lastSizeStr) $size /= 1024;
        }

        if ($sizeStr == $sizes[0]) $result = '%01d %s';  // Bytes aren't normally fractional

        return sprintf($result, $size, $sizeStr);
    }

    /**
     * [getReadableTime description]
     *
     * @param   [type]  $time  [$time description]
     *
     * @return  [type]         [return description]
     */
    public function getReadableTime($time)
    {
        $ret = $time;
        $formatter = 0;

        $formats = array('ms', 's', 'm');

        if(($time >= 1000) && ($time < 60000)) {
            $formatter = 1;

            $ret = ($time / 1000);
        }

        if($time >= 60000) {
            $formatter = 2;

            $ret = ($time / 1000) / 60;
        }

        return number_format($ret, 3, '.', '') .' ' .$formats[$formatter];
    }

    /**
     * [display description]
     *
     * @param   [type] $fetch  [$fetch description]
     * @param   false          [ description]
     *
     * @return  [type]         [return description]
     */
    public function display($fetch = false)
    {
        Console::log(__d('system', 'Forensics - Profiler start gathering the information'));

        // Gather the information.
        $this->gatherFileData();
        $this->gatherMemoryData();
        $this->gatherSQLQueryData();
        $this->gatherFrameworkData();

        Console::logSpeed(__d('system', 'Forensics - Profiler start displaying the information'));

        $this->gatherConsoleData();
        $this->gatherSpeedData();

        // Render the Profiler's widget.
        return $this->render($this->output, $fetch);
    }

    /**
     * [render description]
     *
     * @param   [type]  $output  [$output description]
     * @param   [type]  $fetch   [$fetch description]
     *
     * @return  [type]           [return description]
     */
    function render($output, $fetch)
    {
        // Prepare the information.
        $logCount = count($output['logs']['console']);
        $fileCount = count($output['files']);

        $memoryUsed = $output['memoryTotals']['used'];
        $queryCount = $output['queryTotals']['count'];
        $speedTotal = $output['speedTotals']['total'];

        // Render the associated View Fragment (and return the output, if is the case).
        if($fetch) {
            ob_start();
        }

        require $this->viewPath;

        if($fetch) {
            return ob_get_clean();
        }

        return true;
    }

}
