<?php

namespace Npds\Packages;

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
        $packages = Config::get('packages');

        if (! $packages) {
            return;
        }

        foreach ($packages as $package) {
            $filePath = str_replace('/', DS, BASEPATH.'Packages/'.$package.'/Config/bootstrap.php');

            if (!is_readable($filePath)) {
                continue;
            }

            require $filePath;

            //
            static::boot_config($package);   
        }
    }

    /**
     * [boot_config description]
     *
     * @param   [type]  $package  [$package description]
     *
     * @return  [type]            [return description]
     */
    public static function boot_config($package)
    {
        $filePath = str_replace('/', DS, APPPATH.'Packages/'.$package.'/Config');
        
        // Load the configuration files.
        foreach (glob($filePath.'/*.php') as $path) {
            $key = lcfirst(pathinfo($path, PATHINFO_FILENAME));
            Config::set(strtolower($package).'.'.$key, require($path));
        }
    }

}
