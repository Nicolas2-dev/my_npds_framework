<?php

namespace Npds\Supercache;

use Npds\Config\Config;

/**
 * Undocumented class
 */
class SuperCacheManager
{

    /**
     * [$request_uri description]
     *
     * @var [type]
     */
    protected $request_uri;

    /**
     * [$query_string description]
     *
     * @var [type]
     */
    protected $query_string;

    /**
     * [$php_self description]
     *
     * @var [type]
     */
    protected $php_self;

    /**
     * [$genereting_output description]
     *
     * @var [type]
     */
    protected $genereting_output;

    /**
     * [$site_overload description]
     *
     * @var [type]
     */
    protected $site_overload;

    /**
     * [$instance description]
     *
     * @var [type]
     */
    protected static $instance;

    /**
     * [$cache_config description]
     *
     * @var [type]
     */
    protected static $cache_config = [];

    /**
     * [$cache_timings description]
     *
     * @var [type]
     */
    protected static $cache_timings = [];

    /**
     * [$cache_querys description]
     *
     * @var [type]
     */
    protected static $cache_querys = [];


    /**
     * [getInstance description]
     *
     * @return  [type]  [return description]
     */
    // public static function getInstance()
    // {
    //     if (isset(static::$instance)) {
    //         return static::$instance;
    //     }

    //     return static::$instance = new static();
    // } 

    /**
     * [__construct description]
     *
     * @return  [type]  [return description]
     */
    public function __construct()
    {
        $this->genereting_output = 0;

        if (!empty($_SERVER) && isset($_SERVER['REQUEST_URI'])) {
            $this->request_uri = $_SERVER['REQUEST_URI'];
        } else {
            $this->request_uri = getenv('REQUEST_URI');
        }

        if (!empty($_SERVER) && isset($_SERVER['QUERY_STRING'])) {
            $this->query_string = $_SERVER['QUERY_STRING'];
        } else {
            $this->query_string = getenv('QUERY_STRING');
        }

        if (!empty($_SERVER) && isset($_SERVER['PHP_SELF'])) {
            $this->php_self = basename($_SERVER['PHP_SELF']);
        } else {
            $this->php_self = basename($GLOBALS['PHP_SELF']);
        }

        $this->site_overload = false;

        if (file_exists("storage/cache/site_load.log")) {
            $site_load = file("storage/cache/site_load.log");

            if ($site_load[0] >= Config::get('supercache.clean_limit')) {
                $this->site_overload = true;
            }
        }

        if ((Config::get('supercache.run_cleanup') == 1) and (!$this->site_overload)) {
            $this->cacheCleanup();
        }
    }

    /**
     * [get_Genereting_Output description]
     *
     * @return  [type]  [return description]
     */
    public function get_Genereting_Output()
    {
        return $this->genereting_output;
    }

    /**
     * [set_Genereting_Output description]
     *
     * @param   [type]  $output  [$output description]
     *
     * @return  [type]           [return description]
     */
    public function set_Genereting_Output($output)
    {
        $this->genereting_output = $output;
    }

    /**
     * [startCachingPage description]
     *
     * @return  [type]  [return description]
     */
    public function startCachingPage()
    {
        // if ($this->cache_timings[$this->php_self] > 0 and ($this->query_string == '' or ereg($this->cache_querys[$this->php_self], $this->query_string)) ) {
        if ($this->cache_timings[$this->php_self] > 0 and ($this->query_string == '' or preg_match("#" . $this->cache_querys[$this->php_self] . "#", $this->query_string))) {
            
            $cached_page = $this->checkCache($this->request_uri, $this->cache_timings[$this->php_self]);

            if ($cached_page != '') {
                echo $cached_page;

                global $App_sc;
                $App_sc = true;

                $this->logVisit($this->request_uri, 'HIT');

                if ($this->cache_config['exit'] == 1) {
                    exit;
                }
            } else {
                ob_start();
                $this->genereting_output = 1;
                $this->logVisit($this->request_uri, 'MISS');
            }
        } else {
            $this->logVisit($this->request_uri, 'EXCL');
            $this->genereting_output = -1;
        }
    }

    /**
     * [endCachingPage description]
     *
     * @return  [type]  [return description]
     */
    public function endCachingPage()
    {
        if ($this->genereting_output == 1) {
            $output = ob_get_contents();
            // if you want to activate rewrite engine
            //if (file_exists("config/rewrite_engine.php")) {
            //   include ("config/rewrite_engine.php");
            //}
            ob_end_clean();

            $this->insertIntoCache($output, $this->request_uri);
        }
    }

    /**
     * [checkCache description]
     *
     * @param   [type]  $request  [$request description]
     * @param   [type]  $refresh  [$refresh description]
     *
     * @return  [type]            [return description]
     */
    public function checkCache($request, $refresh)
    {
        global $user;

        if (!$this->cache_config['non_differentiate']) {
            if (isset($user) and $user != '') {
                $cookie = explode(':', base64_decode($user));
                $cookie = $cookie[1];
            } else {
                $cookie = '';
            }
        }

        // the .common is used for non differentiate cache page (same page for user and anonymous)
        if (substr($request, -7) == '.common') {
            $cookie = '';
        }

        $filename = $this->cache_config['data_dir'] . $cookie . md5($request) . '.' . Config::get('npds.language');

        // Overload
        if ($this->site_overload) {
            $refresh = $refresh * 2;
        }

        if (file_exists($filename)) {
            if (filemtime($filename) > time() - $refresh) {
                if (filesize($filename) > 0) {
                    $data = fread($fp = fopen($filename, 'r'), filesize($filename));
                    fclose($fp);

                    return $data;
                } else {
                    return '';
                }
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    /**
     * [insertIntoCache description]
     *
     * @param   [type]  $content  [$content description]
     * @param   [type]  $request  [$request description]
     *
     * @return  [type]            [return description]
     */
    public function insertIntoCache($content, $request)
    {
        global $user;

        if (!$this->cache_config['non_differentiate']) {
            if (isset($user) and $user != '') {
                $cookie = explode(":", base64_decode($user));
                $cookie = $cookie[1];
            } else {
                $cookie = '';
            }
        }

        // the .common is used for non differentiate cache page (same page for user and anonymous)
        if (substr($request, -7) == '.common') {
            $cookie = '';
        }

        if (substr($request, 0, 5) == 'objet') {
            $request = substr($request, 5);
            $affich = false;
        } else {
            $affich = true;
        }

        $nombre = $this->cache_config['data_dir'] . $cookie . md5($request) . '.' . Config::get('npds.language');

        if ($fp = fopen($nombre, 'w')) {
            flock($fp, LOCK_EX);
            fwrite($fp, $content);
            flock($fp, LOCK_UN);
            fclose($fp);
        }

        if ($affich) {
            echo $content;
        }

        global $App_sc;
        $App_sc = false;
    }

    /**
     * [logVisit description]
     *
     * @param   [type]  $request  [$request description]
     * @param   [type]  $type     [$type description]
     *
     * @return  [type]            [return description]
     */
    public function logVisit($request, $type)
    {
        if (!Config::get('supercache.save_stats')) {
            return;
        }

        $logfile = Config::get('supercache.data_dir') .'/'. 'stats.log';

        $fp = fopen($logfile, 'a');
        flock($fp, LOCK_EX);
        fseek($fp, filesize($logfile));

        $salida = sprintf("%-10s %-74s %-4s\r\n", time(), $request, $type);

        fwrite($fp, $salida);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * [cacheCleanup description]
     *
     * @return  [type]  [return description]
     */
    public function cacheCleanup()
    {
        // Cette fonction n'est plus adaptée au nombre de fichiers manipulé par SuperCache
        srand((float)microtime() * 1000000);

        $num = rand(1, 100);

        if ($num <= Config::get('supercache.cleanup_freq')) {
            $dh = opendir(Config::get('supercache.data_dir'));
            $clean = false;

            // Clean SC directory
            $objet = "SC";
            while (false !== ($filename = readdir($dh))) {
                if ($filename === '.' or $filename === '..' or $filename === 'sql' or $filename === 'index.html') {
                    continue;
                }

                if (filemtime(Config::get('supercache.data_dir') .'/'. $filename) < time() - Config::get('supercache.max_age')) {
                    @unlink(Config::get('supercache.data_dir') .'/'. $filename);
                    $clean = true;
                }
            }
            closedir($dh);

            // Clean SC/SQL directory
            $dh = opendir(Config::get('supercache.data_dir') .'/'. "sql/");
            $objet .= "+SQL";

            while (false !== ($filename = readdir($dh))) {
                if ($filename === '.' or $filename === '..') {
                    continue;
                }

                if (filemtime(Config::get('supercache.data_dir') .'/'. "sql/" . $filename) < time() - Config::get('supercache.max_age')) {
                    @unlink(Config::get('supercache.data_dir') .'/'. "sql/" . $filename);
                    $clean = true;
                }
            }
            closedir($dh);

            $fp = fopen(Config::get('supercache.data_dir') .'/'. "sql/.htaccess", 'w');
            @fputs($fp, "Deny from All");
            fclose($fp);

            if ($clean) {
                $this->logVisit($this->request_uri, 'CLEAN ' . $objet);
            }
        }
    }

    /**
     * [UsercacheCleanup description]
     *
     * @return  [type]  [return description]
     */
    public function UsercacheCleanup()
    {
        global $user;

        if (isset($user)) {
            $cookie = explode(":", base64_decode($user));
        }

        $dh = opendir(Config::get('supercache.data_dir'));
        while (false !== ($filename = readdir($dh))) {
            if ($filename === '.' or $filename === '..') {
                continue;
            }

            // Le fichier appartient-il à l'utilisateur connecté ?
            if (substr($filename, 0, strlen($cookie[1])) == $cookie[1]) {

                // Le calcul md5 fournit une chaine de 32 chars donc si ce n'est pas 32 c'est que c'est un homonyme ...
                $filename_final = explode(".", $filename);

                if (strlen(substr($filename_final[0], strlen($cookie[1]))) == 32) {
                    unlink(Config::get('supercache.data_dir') .'/'. $filename);
                }
            }
        }
        closedir($dh);
    }

    /**
     * [startCachingBlock description]
     *
     * @param   [type]  $Xblock  [$Xblock description]
     *
     * @return  [type]           [return description]
     */
    public function startCachingBlock($Xblock)
    {
        if ($this->cache_timings[$Xblock] > 0) {
            $cached_page = $this->checkCache($Xblock, $this->cache_timings[$Xblock]);

            if ($cached_page != '') {
                echo $cached_page;
                $this->logVisit($Xblock, 'HIT');

                if (Config::get('supercache.exit') == 1) {
                    exit;
                }
            } else {
                ob_start();
                $this->genereting_output = 1;
                $this->logVisit($Xblock, 'MISS');
            }
        } else {
            $this->genereting_output = -1;
            $this->logVisit($Xblock, 'NO-CACHE');
        }
    }

    /**
     * [endCachingBlock description]
     *
     * @param   [type]  $Xblock  [$Xblock description]
     *
     * @return  [type]           [return description]
     */
    public function endCachingBlock($Xblock)
    {
        if ($this->genereting_output == 1) {
            $output = ob_get_contents();
            ob_end_clean();
            $this->insertIntoCache($output, $Xblock);
        }
    }

    /**
     * [CachingQuery description]
     *
     * @param   [type]  $Xquery     [$Xquery description]
     * @param   [type]  $retention  [$retention description]
     *
     * @return  [type]              [return description]
     */
    public function CachingQuery($Xquery, $retention)
    {
        $filename = $this->cache_config['data_dir'] . "sql/" . md5($Xquery);

        if (file_exists($filename)) {
            if (filemtime($filename) > time() - $retention) {
                if (filesize($filename) > 0) {
                    $data = fread($fp = fopen($filename, 'r'), filesize($filename));
                    fclose($fp);
                } else {
                    return array();
                }

                $no_cache = false;
                $this->logVisit($Xquery, 'HIT');

                return unserialize($data);
            } else
                $no_cache = true;
        } else
            $no_cache = true;

        if ($no_cache) {
            $result = @sql_query($Xquery);
            $tab_tmp = array();

            while ($row = sql_fetch_assoc($result)) {
                $tab_tmp[] = $row;
            }

            if ($fp = fopen($filename, 'w')) {
                flock($fp, LOCK_EX);
                fwrite($fp, serialize($tab_tmp));
                flock($fp, LOCK_UN);
                fclose($fp);
            }

            $this->logVisit($Xquery, 'MISS');

            return $tab_tmp;
        }
    }

    /**
     * [startCachingObjet description]
     *
     * @param   [type]  $Xobjet  [$Xobjet description]
     *
     * @return  [type]           [return description]
     */
    public function startCachingObjet($Xobjet)
    {
        if ($this->cache_timings[$Xobjet] > 0) {
            $cached_page = $this->checkCache($Xobjet, $this->cache_timings[$Xobjet]);

            if ($cached_page != '') {
                $this->logVisit($Xobjet, 'HIT');

                if ($this->cache_config['exit'] == 1) {
                    exit;
                }

                return unserialize($cached_page);
            } else {
                $this->genereting_output = 1;
                $this->logVisit($Xobjet, 'MISS');

                return "";
            }
        } else {
            $this->genereting_output = -1;
            $this->logVisit($Xobjet, 'NO-CACHE');

            return "";
        }
    }

    /**
     * [endCachingObjet description]
     *
     * @param   [type]  $Xobjet  [$Xobjet description]
     * @param   [type]  $Xtab    [$Xtab description]
     *
     * @return  [type]           [return description]
     */
    public function endCachingObjet($Xobjet, $Xtab)
    {
        if ($this->genereting_output == 1) {
            $this->insertIntoCache(serialize($Xtab), "objet" . $Xobjet);
        }
    }

}
