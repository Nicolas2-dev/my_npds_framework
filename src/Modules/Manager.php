<?php

namespace Npds\Modules;

use Npds\Config\Config;

/**
 * Undocumented class
 */
class Manager
{
    
    /**
     * [bootstrap description]
     *
     * @return  [type]  [return description]
     */
    public static function bootstrap()
    {
        $modules = Config::get('modules');

        if (! $modules) {
            return;
        }

        foreach ($modules as $module) {

            //
            $filePath = str_replace('/', DS, BASEPATH.'Modules/'.$module.'/Bootstrap/bootstrap.php');

            if (!is_readable($filePath)) {
                continue;
            }

            require $filePath;

            static::boot_config($module);
        }
    }

    /**
     * [boot_config description]
     *
     * @param   [type]  $module  [$module description]
     *
     * @return  [type]           [return description]
     */
    public static function boot_config($module)
    {
        $filePath = str_replace('/', DS, APPPATH.'Modules/'.$module.'/Config');
        
        // Load the configuration files.
        foreach (glob($filePath.'/*.php') as $path) {
            $key = lcfirst(pathinfo($path, PATHINFO_FILENAME));
            Config::set(strtolower($module).'.'.$key, require($path));
        }
    }

}
