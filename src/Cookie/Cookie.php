<?php

namespace Npds\Cookie;

/**
 * [Cookie description]
 */
class Cookie
{

    /**
     * [FOURYEARS description]
     *
     * @var [type]
     */
    const FOURYEARS = 126144000;


    /**
     * [exists description]
     *
     * @param   [type]  $key  [$key description]
     *
     * @return  [type]        [return description]
     */
    public static function exists($key)
    {
        return isset($_COOKIE[$key]);
    }

    /**
     * [set description]
     *
     * @param   [type]     $key     [$key description]
     * @param   [type]     $value   [$value description]
     * @param   [type]     $expiry  [$expiry description]
     * @param   self                [ description]
     * @param   FOURYEARS  $path    [$path description]
     * @param   [type]     $domain  [$domain description]
     * @param   false               [ description]
     *
     * @return  [type]              [return description]
     */
    public static function set($key, $value, $expiry = self::FOURYEARS, $path = '/', $domain = false)
    {
        $retval = false;

        // Ensure to have a valid domain.
        $domain = ($domain !== false) ? $domain : $_SERVER['HTTP_HOST'];

        if (! headers_sent()) {
            if ($expiry === -1) {
                $expiry = 1893456000; // Lifetime = 2030-01-01 00:00:00
            } else if (is_numeric($expiry)) {
                $expiry += time();
            } else {
                $expiry = strtotime($expiry);
            }

            $retval = @setcookie($key, $value, $expiry, $path, $domain);

            if ($retval) {
                $_COOKIE[$key] = $value;
            }
        }

        return $retval;
    }

    /**
     * [get description]
     *
     * @param   [type]  $key      [$key description]
     * @param   [type]  $default  [$default description]
     *
     * @return  [type]            [return description]
     */
    public static function get($key, $default = '')
    {
        return (isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default);
    }

    /**
     * [display description]
     *
     * @return  [type]  [return description]
     */
    public static function display()
    {
        return $_COOKIE;
    }

    /**
     * [destroy description]
     *
     * @param   [type] $key     [$key description]
     * @param   [type] $path    [$path description]
     * @param   [type] $domain  [$domain description]
     * @param   false           [ description]
     *
     * @return  [type]          [return description]
     */
    public static function destroy($key, $path = '/', $domain = false)
    {
        // Ensure to have a valid domain.
        $domain = ($domain !== false) ? $domain : $_SERVER['HTTP_HOST'];

        if (! headers_sent()) {
            unset($_COOKIE[$key]);

            // To delete the Cookie we set its expiration four years into past.
            @setcookie($key, '', time() - self::FOURYEARS, $path, $domain);
        }
    }

}
