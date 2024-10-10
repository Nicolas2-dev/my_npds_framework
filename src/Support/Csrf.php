<?php

namespace Npds\Support;

use Npds\Session\Session;

/**
 * Undocumented class
 */
class Csrf
{

    /**
     * Undocumented function
     *
     * @param string $name
     * @return void
     */
    public static function makeToken($name = 'csrfToken')
    {
        $max_time = 60 * 60 * 24; // token is valid for 1 day

        $csrf_token  = Session::get($name);
        $stored_time = Session::get($name .'_time');

        if ((($max_time + $stored_time) <= time()) || empty($csrf_token)) {
            Session::set($name, md5(uniqid(rand(), true)));
            Session::set($name .'_time', time());
        }

        return Session::get($name);
    }

    /**
     * Undocumented function
     *
     * @param string $name
     * @return boolean
     */
    public static function isTokenValid($name = 'csrfToken')
    {
        return ($_POST[$name] === Session::get($name));
    }
    
}
