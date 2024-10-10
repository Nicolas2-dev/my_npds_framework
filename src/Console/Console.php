<?php

namespace Npds\Console;

/**
 * [Console description]
 */
class Console
{
    /**
     * [$logs description]
     *
     * @var [type]
     */
    private static $logs = array(
        'console'     => array(),
        'logCount'    => 0,
        'memoryCount' => 0,
        'errorCount'  => 0,
        'speedCount'  => 0,
    );

    /**
     * [log description]
     *
     * @param   [type]  $data  [$data description]
     *
     * @return  [type]         [return description]
     */
    public static function log($data)
    {
        $logItem = array(
            "data"  => $data,
            "type"  => 'log'
        );

        self::$logs['console'][] = $logItem;

        self::$logs['logCount'] += 1;
    }

    /**
     * [logMemory description]
     *
     * @param   [type] $object  [$object description]
     * @param   false  $name    [$name description]
     * @param   PHP             [ description]
     *
     * @return  [type]          [return description]
     */
    public static function logMemory($object = false, $name = 'PHP')
    {
        $memory = memory_get_usage();

        if($object) $memory = strlen(serialize($object));

        $logItem = array(
            "data"      => $memory,
            "type"      => 'memory',
            "name"      => $name,
            "dataType"  => gettype($object)
        );

        self::$logs['console'][] = $logItem;

        self::$logs['memoryCount'] += 1;
    }

    /**
     * [logError description]
     *
     * @param   [type]  $exception  [$exception description]
     * @param   [type]  $message    [$message description]
     *
     * @return  [type]              [return description]
     */
    public static function logError($exception, $message)
    {
        $logItem = array(
            "data"  => $message,
            "type"  => 'error',
            "file"  => $exception->getFile(),
            "line"  => $exception->getLine()
        );

        self::$logs['console'][] = $logItem;

        self::$logs['errorCount'] += 1;
    }

    /**
     * [logSpeed description]
     *
     * @param   [type]  $name  [$name description]
     *
     * @return  [type]         [return description]
     */
    public static function logSpeed($name = 'Point in Time')
    {
        $logItem = array(
            "data"  => microtime(true),
            "type"  => 'speed',
            "name"  => $name
        );

        self::$logs['console'][] = $logItem;

        self::$logs['speedCount'] += 1;
    }

    /**
     * [getLogs description]
     *
     * @return  [type]  [return description]
     */
    public static function getLogs()
    {
        return self::$logs;
    }

}
