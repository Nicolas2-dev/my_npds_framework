<?php

namespace Npds\Modules;

use Npds\Config\Config;
use Npds\Foundation\AliasLoader;

/**
 * Undocumented class
 */
class Manager
{
    
    /**
     * [$instance description]
     *
     * @var [type]
     */
    protected static $instance;

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected static $Kernel = [];

    /**
     * 
     */
    protected static $module_path;
    
    /**
     * Undocumented variable
     *
     * @var array
     */
    protected static $modules = [];

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected static $bootstrappers_module = [
        'load_config',
        'load_aliases',         
        'load_bootstrap',
        'load_constant',
        'load_helper',
        'load_boxe',
        'load_events',
        'load_route_web',
        'load_route_admin',
        'load_route_api',
    ];

    /**
     * Undocumented variable
     *
     * @var boolean
     */
    protected static $has_module_been_bootstrapped = [];


    /**
     * Undocumented function
     */
    public function __construct($directory)
    {
        static::$module_path = $directory;

        static::$modules = Config::get('modules');
    }

    /**
     * [getInstance description]
     *
     * @return  [type]  [return description]
     */
    public static function module_path($directory)
    {
        if (isset(static::$instance)) {
            return static::$instance;
        }

        return static::$instance = new static($directory);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public static function register()
    {
        if (! static::$modules) {
            return;
        }

        foreach (static::$modules as $module) {
            
            $classKernel = '\\Modules\\'.ucfirst($module).'\\Bootstrap\\'.ucfirst($module).'Kernel';
              
            if(class_exists($classKernel) && method_exists($classKernel, 'getInstance')) {

                static::$Kernel = [ucfirst($module) => with($classKernel::getInstance(static::$module_path .ucfirst($module). DS))];

                static::$has_module_been_bootstrapped = [
                    ucfirst($module) => false
                ];

                static::bootstrap_module(ucfirst($module));
            }

            if (isset(static::$Kernel[$module]::$boot_method))  {
                static::register_boot_method($module);
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @return void
     */
    private static function register_boot_method($module)
    {
        foreach (static::$Kernel[$module]::$boot_method as $module_boot_method) {
            call_user_func_array([static::$Kernel[$module], 'register_'.$module_boot_method], []);
        }
    }


    /**
     * Bootstrap the application for HTTP requests.
     *
     * @return void
     */
    public static function bootstrap_module($module)
    {
        if (! static::has_module_been_bootstrapped($module)) {
            static::bootstrap_module_with(static::bootstrappers_module(), $module);
        }
    }

    /**
     * Undocumented function
     *
     * @param array $bootstrappers
     * @param [type] $module
     * @return void
     */
    public static function bootstrap_module_with(array $bootstrappers, $module)
    {
        foreach ($bootstrappers as $bootstrapper) {
            static::$bootstrapper($module);
        }

        static::$has_module_been_bootstrapped = [
            $module => true
        ];
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public static function has_module_been_bootstrapped($module)
    {
        return static::$has_module_been_bootstrapped[$module];
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    protected static function bootstrappers_module()
    {
        return static::$bootstrappers_module;
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @return void
     */
    private static function load_aliases($module) 
    {
        AliasLoader::getInstance(static::$Kernel[$module]::$aliases)->register();
    } 

    /**
     * [boot_config description]
     *
     * @param   [type]  $module  [$module description]
     *
     * @return  [type]           [return description]
     */
    public static function load_config($module)
    {
        $filePath = str_replace('/', DS, static::$module_path . $module.'/Config');
        
        // Load the configuration files.
        foreach (glob($filePath.'/*.php') as $path) {
            $key = lcfirst(pathinfo($path, PATHINFO_FILENAME));
            
            Config::set(strtolower($module).'.'.$key, require($path));
        }
    }

    /**
     * [bootstrap description]
     *
     * @return  [type]  [return description]
     */
    public static function load_bootstrap($module)
    {
        static::load_file('bootstrap.php', $module);
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @return void
     */
    private static function load_constant($module)
    {
        static::load_file('constants.php', $module);
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @return void
     */
    private static function load_helper($module)
    {
        static::load_file('Support/helpers.php', $module);
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @return void
     */
    private static function load_boxe($module)
    {
        static::load_file('Boxe/Boxe.php', $module);
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @return void
     */
    private static function load_events($module)
    {
        static::load_file('Events/events.php', $module);
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @return void
     */
    private static function load_route_web($module)
    {
        static::load_file('Routes/web/routes.php', $module);
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @return void
     */
    private static function load_route_admin($module)
    {
        static::load_file('Routes/admin/routes.php', $module);
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @return void
     */
    private static function load_route_api($module)
    {
        static::load_file('Routes/api/routes.php', $module);
    }

    /**
     * Undocumented function
     *
     * @param [type] $file
     * @return void
     */
    private static function load_file($file, $module)
    {
        $filePath = str_replace('/', DS, static::$module_path . $module .'/'. $file);

        if (is_readable($filePath)) {
            require $filePath;
        }
    }

}
