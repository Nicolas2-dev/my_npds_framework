<?php

namespace Npds\Support;

/**
 * Undocumented class
 */
class Data
{

    /**
     * Undocumented function
     *
     * @param [type] $data
     * @return void
     */
    public static function pr($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }

    /**
     * Undocumented function
     *
     * @param [type] $data
     * @return void
     */
    public static function vd($data)
    {
        var_dump($data);
    }

    /**
     * Undocumented function
     *
     * @param [type] $data
     * @return void
     */
    public static function sl($data)
    {
        return strlen($data);
    }

    /**
     * Undocumented function
     *
     * @param [type] $data
     * @return void
     */
    public static function stu($data)
    {
        return strtoupper($data);
    }

    /**
     * Undocumented function
     *
     * @param [type] $data
     * @return void
     */
    public static function stl($data)
    {
        return strtolower($data);
    }

    /**
     * Undocumented function
     *
     * @param [type] $data
     * @return void
     */
    public static function ucw($data)
    {
        return ucwords($data);
    }

    /**
     * Undocumented function
     *
     * @param integer $length
     * @return void
     */
    public static function createKey($length = 32)
    {
        $chars = "!@#$%^&*()_+-=ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
        $key = "";

        for ($i = 0; $i < $length; $i++) {
            $key .= $chars.rand(0, strlen($chars) - 1);
        }

        return $key;
    }
    
}
