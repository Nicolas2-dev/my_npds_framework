<?php

namespace Npds\Session;

/**
 * Undocumented class
 */
class Session
{

    /**
     * [$sessionStarted description]
     *
     * @var [type]
     */
    private static $sessionStarted = false;


    /**
     * [initialize description]
     *
     * @return  [type]  [return description]
     */
    public static function initialize()
    {
        if (self::$sessionStarted == false) {
            session_start();

            self::$sessionStarted = true;
        }
    }

    /**
     * [exists description]
     *
     * @param   [type]  $key  [$key description]
     *
     * @return  [type]        [return description]
     */
    public static function exists($key)
    {
        if (isset($_SESSION[SESSION_PREFIX.$key])) {
            return true;
        }

        return false;
    }

    /**
     * [set description]
     *
     * @param   [type] $key    [$key description]
     * @param   [type] $value  [$value description]
     * @param   false          [ description]
     *
     * @return  [type]         [return description]
     */
    public static function set($key, $value = false)
    {
        /**
        * Check whether session is set in array or not
        * If array then set all session key-values in foreach loop
        */
        if (is_array($key) && $value === false) {
            foreach ($key as $name => $value) {
                $_SESSION[SESSION_PREFIX.$name] = $value;
            }
        } else {
            $_SESSION[SESSION_PREFIX.$key] = $value;
        }
    }

    /**
     * [pull description]
     *
     * @param   [type]  $key  [$key description]
     *
     * @return  [type]        [return description]
     */
    public static function pull($key)
    {
        if (isset($_SESSION[SESSION_PREFIX.$key])) {
            $value = $_SESSION[SESSION_PREFIX.$key];
            unset($_SESSION[SESSION_PREFIX.$key]);
            return $value;
        }
        return null;
    }

    /**
     * [get description]
     *
     * @param   [type] $key        [$key description]
     * @param   [type] $secondkey  [$secondkey description]
     * @param   false              [ description]
     *
     * @return  [type]             [return description]
     */
    public static function get($key, $secondkey = false)
    {
        if ($secondkey == true) {
            if (isset($_SESSION[SESSION_PREFIX.$key][$secondkey])) {
                return $_SESSION[SESSION_PREFIX.$key][$secondkey];
            }
        } else {
            if (isset($_SESSION[SESSION_PREFIX.$key])) {
                return $_SESSION[SESSION_PREFIX.$key];
            }
        }
        return null;
    }

    /**
     * [id description]
     *
     * @return  [type]  [return description]
     */
    public static function id()
    {
        return session_id();
    }

    /**
     * [regenerate description]
     *
     * @return  [type]  [return description]
     */
    public static function regenerate()
    {
        session_regenerate_id(true);
        return session_id();
    }

    /**
     * [display description]
     *
     * @return  [type]  [return description]
     */
    public static function display()
    {
        return $_SESSION;
    }

    /**
     * [destroy description]
     *
     * @param   [type] $key     [$key description]
     * @param   [type] $prefix  [$prefix description]
     * @param   false           [ description]
     *
     * @return  [type]          [return description]
     */
    public static function destroy($key = '', $prefix = false)
    {
        /** only run if session has started */
        if (self::$sessionStarted == true) {
            /** if key is empty and $prefix is false */
            if ($key =='' && $prefix == false) {
                session_unset();
                session_destroy();
            } elseif ($prefix == true) {
                /** clear all session for set SESSION_PREFIX */
                foreach ($_SESSION as $key => $value) {
                    if (strpos($key, SESSION_PREFIX) === 0) {
                        unset($_SESSION[$key]);
                    }
                }
            } else {
                /** clear specified session key */
                unset($_SESSION[SESSION_PREFIX.$key]);
            }
        }
    }

    /**
     * [message description]
     *
     * @param   [type]   $sessionName  [$sessionName description]
     * @param   success                [ description]
     *
     * @return  [type]                 [return description]
     */
    public static function message($sessionName = 'success')
    {
        $data = Session::pull($sessionName);

        if (empty($data)) {
            // Let's make Tom happy!
            return null;
        }

        if (! is_array($data)) {
            // The message is structured in the Default style.
            $alertType = $sessionName;
            $alertText = $data;
        } else {
            // The message is structured in the Hadrianus style.
            $alertType = $data['type'];
            $alertText = $data['text'];
        }

        switch($alertType) {
            case 'success':
                $alertIcon = 'check';
                break;
            case 'warning':
                $alertIcon = 'warning';
                break;
            case 'danger':
                $alertIcon = 'bomb';
                break;
            case 'info':
            default:
                $alertIcon = 'info';
        }

        return "<div class='alert alert-".$alertType." align-items-center alert-dismissible fade show' role='alert'>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    <i class='fa fa-".$alertIcon."'></i> ".$alertText."
                </div>";
    }
    
}
