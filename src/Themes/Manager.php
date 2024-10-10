<?php

namespace Npds\Themes;

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
        //
        static::bootstrap_frontent();

        //
        static::bootstrap_backend();
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public static function bootstrap_frontent()
    {
        $themes = Config::get('themes.frontend');

        if (! $themes) {
            return;
        }

        foreach ($themes as $theme) {
            $filePath = str_replace('/', DS, APPPATH.'Themes/Frontend/'.$theme.'/Bootstrap/bootstrap.php');

            if (!is_readable($filePath)) {
                continue;
            }

            require $filePath;

            //
            static::boot_config_frontend($theme);
        }
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public static function bootstrap_backend()
    {
        $themes = Config::get('themes.backend');

        if (! $themes) {
            return;
        }

        foreach ($themes as $theme) {
            $filePath = str_replace('/', DS, APPPATH.'Themes/Backend/'.$theme.'/Bootstrap/bootstrap.php');

            if (!is_readable($filePath)) {
                continue;
            }

            require $filePath;

            static::boot_config_backend($theme);
        }
    }

    /**
     * [boot_config description]
     *
     * @param   [type]  $theme  [$module description]
     *
     * @return  [type]           [return description]
     */
    public static function boot_config_frontend($theme)
    {
        $filePath = str_replace('/', DS, APPPATH.'Themes/Frontend/'.$theme.'/Config');
        
        // Load the configuration files.
        foreach (glob($filePath.'/*.php') as $path) {
            $key = lcfirst(pathinfo($path, PATHINFO_FILENAME));
            Config::set(strtolower($theme).'.'.$key, require($path));
        }
    }

    /**
     * [boot_config description]
     *
     * @param   [type]  $theme  [$module description]
     *
     * @return  [type]           [return description]
     */
    public static function boot_config_backend($theme)
    {
        $filePath = str_replace('/', DS, APPPATH.'Themes/Backend/'.$theme.'/Config');
        
        // Load the configuration files.
        foreach (glob($filePath.'/*.php') as $path) {
            $key = lcfirst(pathinfo($path, PATHINFO_FILENAME));
            Config::set(strtolower($theme).'.'.$key, require($path));
        }
    }

}
