<?php

namespace Npds\Modules;

use Npds\Http\Request;
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
        'load_event',
        'load_helper',
        'load_boxe',
        'load_route_web',
        'load_route_web_filter',        
        'load_route_admin',
        'load_route_admin_filter',        
        'load_route_api',
        'load_route_api_filter',        
    ];

    /**
     * Undocumented variable
     *
     * @var boolean
     */
    protected static $has_module_been_bootstrapped = [];

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected static $bootedCallbacks = array();

    /**
     * 
     */
    protected static Request $request;


    /**
     * Undocumented function
     */
    public function __construct($directory)
    {
        static::$module_path = $directory;

        static::$modules = Config::get('modules');

        static::$request = new Request();
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
            //
            static::load_module_bootstrap($module);
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @return object
     */
    public static function kernel($module)
    {
        return static::$Kernel[ucfirst($module)];
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @return string
     */
    private static function kernel_modul_class($module)
    {
        return 'Modules\\'.ucfirst($module).'\Bootstrap\\'.ucfirst($module).'Kernel';
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @return void
     */
    private static function load_module_bootstrap($module)
    {
        $classKernel = static::kernel_modul_class($module);
              
        if(class_exists($classKernel) && method_exists($classKernel, 'getInstance')) {

            static::$Kernel = [$module => with($classKernel::getInstance(static::$module_path .$module. DS))];

            foreach (static::$bootstrappers_module as $bootstrapper) {

               static::$bootedCallbacks[$module]['booststrap'][$bootstrapper] = false;

                if (!static::$bootedCallbacks[$module]['booststrap'][$bootstrapper]) {

                    call_user_func_array([static::class, $bootstrapper], [$module, static::$request]);

                    static::$bootedCallbacks[$module]['booststrap'][$bootstrapper] = true;
                }
             
            }

            // 
            static::load_module_boot_method($module);
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @return void
     */
    private static function load_module_boot_method($module)
    {
        if (isset(static::$Kernel[$module]::$boot_method)){
            foreach(static::$Kernel[$module]::$boot_method as $bootstrapper) {

                static::$bootedCallbacks[$module]['functions']['register_'.$bootstrapper] = false;

                if (!static::$bootedCallbacks[$module]['functions']['register_'.$bootstrapper]) {

                    call_user_func_array([static::$Kernel[$module], 'register_'.$bootstrapper], [static::$request, static::$Kernel[$module]]);

                    static::$bootedCallbacks[$module]['functions']['register_'.$bootstrapper] = true;
                }
            }
        }
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
    private static function load_config($module)
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
    private static function load_bootstrap($module)
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
    private static function load_event($module)
    {
        static::load_file('Events/events.php', $module);
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
    private static function load_route_web($module)
    {
        static::load_file('routes/web/routes.php', $module);
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
     * @param [type] $module
     * @return void
     */
    private static function load_route_web_filter($module)
    {
        static::load_file('Routes/web/filters.php', $module);
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @return void
     */
    private static function load_route_admin_filter($module)
    {
        static::load_file('Routes/admin/filters.php', $module);
    }

    /**
     * Undocumented function
     *
     * @param [type] $module
     * @return void
     */
    private static function load_route_api_filter($module)
    {
        static::load_file('Routes/api/filters.php', $module);
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
