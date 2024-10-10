<?php

namespace Npds\Http;

/**
 * Undocumented class
 */
class Request
{

    /**
     * [getMethod description]
     *
     * @return  [type]  [return description]
     */
    public static function getMethod()
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
        } elseif (isset($_REQUEST['_method'])) {
            $method = $_REQUEST['_method'];
        }

        return strtoupper($method);
    }

    /**
     * [realIpAddr description]
     *
     * @return  [type]  [return description]
     */
    public static function realIpAddr()
    {
        if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * [getip description]
     *
     * @return  [type]  [return description]
     */
    public static function getip()
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $realip = getenv('HTTP_CLIENT_IP');
            } else {
                $realip = getenv('REMOTE_ADDR');
            }
        }

        if (strpos($realip, ",") > 0) {
            $realip = substr($realip, 0, strpos($realip, ",") - 1);
        }

        // from Gu1ll4um3r0m41n - 08-05-2007 - dev 2012
        return urlencode(trim($realip));
    }

    /**
     * [post description]
     *
     * @param   [type]  $key  [$key description]
     *
     * @return  [type]        [return description]
     */
    public static function post($key = null, $default = null)
    {
        if ($key === null) {
            return isset($_POST) ? $_POST : null;
        }

        return array_key_exists($key, $_POST)? $_POST[$key] : $default;
    }

    /**
     * [files description]
     *
     * @param   [type]  $key  [$key description]
     *
     * @return  [type]        [return description]
     */
    public static function files($key = null)
    {
        if ($key === null) {
            return isset($_FILES) ? $_FILES : null;
        }

        return array_key_exists($key, $_FILES)? $_FILES[$key] : null;
    }

    /**
     * [query description]
     *
     * @param   [type]  $key      [$key description]
     * @param   [type]  $default  [$default description]
     *
     * @return  [type]            [return description]
     */
    public static function query($key = null, $default = '')
    {
        if ($key === null) {
            return isset($_GET) ? $_GET : null;
        }

        if (!empty($default)) {
            $default = null;
        }

        return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
    }

   /**
    * [put description]
    *
    * @param   [type]  $key  [$key description]
    *
    * @return  [type]        [return description]
    */
    public static function put($key = null)
    {
        parse_str(file_get_contents("php://input"), $_PUT);

        if ($key == null) {
            return isset($_PUT) ? $_PUT : null;
        }

        return array_key_exists($key, $_PUT) ? $_PUT[$key] : null;
    }

    /**
     * [delete description]
     *
     * @param   [type]  $key  [$key description]
     *
     * @return  [type]        [return description]
     */
    public static function delete($key)
    {
        parse_str(file_get_contents("php://input"), $_DELETE);

        return array_key_exists($key, $_DELETE) ? $_DELETE[$key] : null;
    }

    /**
     * [isAjax description]
     *
     * @return  [type]  [return description]
     */
    public static function isAjax()
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        }
        return false;
    }

    /**
     * [isPost description]
     *
     * @return  [type]  [return description]
     */
    public static function isPost()
    {
        return $_SERVER["REQUEST_METHOD"] === "POST";
    }

    /**
     * [isGet description]
     *
     * @return  [type]  [return description]
     */
    public static function isGet()
    {
        return $_SERVER["REQUEST_METHOD"] === "GET";
    }

    /**
     * [isPut description]
     *
     * @return  [type]  [return description]
     */
    public static function isPut()
    {
        return $_SERVER["REQUEST_METHOD"] === "PUT";
    }

    /**
     * [isDelete description]
     *
     * @return  [type]  [return description]
     */
    public static function isDelete()
    {
        return $_SERVER["REQUEST_METHOD"] === "DELETE";
    }
    
}
