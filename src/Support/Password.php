<?php

namespace Npds\Support;

/**
 * Undocumented class
 */
class Password
{

    /**
     * Undocumented function
     *
     * @param [type] $password
     * @param [type] $algo
     * @param array $options
     * @return void
     */
    public static function make($password, $algo = PASSWORD_DEFAULT, array $options = array())
    {
        return password_hash($password, $algo, $options);
    }

    /**
     * Undocumented function
     *
     * @param [type] $hash
     * @return void
     */
    public static function getInfos($hash)
    {
        return password_get_info($hash);
    }

    /**
     * Undocumented function
     *
     * @param [type] $hash
     * @param [type] $algo
     * @param array $options
     * @return void
     */
    public static function needsRehash($hash, $algo = PASSWORD_DEFAULT, array $options = array())
    {
        return password_needs_rehash($hash, $algo, $options);
    }

    /**
     * Undocumented function
     *
     * @param [type] $password
     * @param [type] $hash
     * @return void
     */
    public static function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
}
