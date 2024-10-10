<?php

namespace Npds\Cache;

use \phpFastCache;
use Npds\Config\Config;

/**
 * [Manager description]
 */
class Manager
{
    
    /**
     * [$cache description]
     *
     * @var [type]
     */
    protected $cache = null;

    /**
     * [$instances description]
     *
     * @var [type]
     */
    protected static $instances = array();


    /**
     * [__construct description]
     *
     * @param   [type]  $storage  [$storage description]
     *
     * @return  [type]            [return description]
     */
    protected function __construct($storage = '', $config = [])
    {
        $config = Config::get('cache');

        $config['storage'] = $storage;

        $storage = strtolower($storage);

        if (($storage == '') || ($storage == 'auto')) {
            $storage = phpFastCache::getAutoClass($config);
        }

        $this->cache = phpFastCache($storage, $config);
    }

    /**
     * [getCache description]
     *
     * @param   [type] $storage  [$storage description]
     * @param   files            [ description]
     *
     * @return  [type]           [return description]
     */
    public static function getCache($storage = 'files')
    {
        if (! isset(self::$instances[$storage])) {
            self::$instances[$storage] = new self($storage);
        }

        return self::$instances[$storage];
    }

    /**
     * [__call description]
     *
     * @param   [type]  $method  [$method description]
     * @param   [type]  $params  [$params description]
     *
     * @return  [type]           [return description]
     */
    public function __call($method, $params = null)
    {
        if (method_exists($this->cache, $method)) {
            return call_user_func_array(array($this->cache, $method), $params);
        }
    }

}
