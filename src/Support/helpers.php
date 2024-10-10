<?php

use Npds\View\View;
use Npds\Config\Config;
use Npds\Core\Controller;
use Npds\Language\Language;
use Npds\Foundation\Container;
use Npds\Support\Debug\Dumper;
use Npds\Exceptions\Http\HttpException;

/**
 * Get controller instance
 *
 * @return \Npds\Core\Controller
 */
function &get_instance()
{
    return Controller::getInstance();
}

/**
 * Undocumented function
 *
 * @param [type] $abstract
 * @param array $parameters
 * @return void
 */
function app($abstract = null, array $parameters = [])
{
    if (is_null($abstract)) {
        return Container::getInstance();
    }

    return Container::getInstance()->make($abstract, $parameters);
}


/**
 * View creation helper
 *
 * @param string $view
 * @param array $data
 * @return \Npds\Core\View
 */
function view($view = null, array $data = array())
{
    return View::make($view, $data);
}

/**
 * View Fragment rendering helper
 *
 * @param string $fragment
 * @param array $data
 * @return \Npds\Core\View
 */
function fragment($fragment, array $data = array())
{
    return View::fragment($fragment, $data);
}

/**
 * Render the given View.
 *
 * @param  string  $view
 * @param  array   $data
 * @return string
 */
function render($view, array $data = array())
{
    if (is_null($view)) return '';

    return View::make($view, $data)->fetch();
}

/**
 * Site url helper
 * @param string $path
 * @return string
 */
function site_url($path = '')
{
    return Config::get('app.url').ltrim($path, '/');
}

/**
 * [base_path description]
 *
 * @param   [type]  $path  [$path description]
 *
 * @return  [type]         [return description]
 */
function base_path($path = '')
{
    return BASEPATH .(! isset($path) ? DS .$path : $path);
}

/**
 * Undocumented function
 *
 * @param string $path
 * @return string
 */
function web_path($path = '')
{
    return WEBPATH .(! isset($path) ? DS .$path : $path);
}

/**
 * [app_path description]
 *
 * @param   [type]  $path  [$path description]
 *
 * @return  [type]         [return description]
 */
function app_path($path = '')
{
    return APPPATH .(! isset($path) ? DS .$path : $path);
}

/**
 * [theme_path description]
 *
 * @param   [type]  $path  [$path description]
 *
 * @return  [type]         [return description]
 */
function theme_path($path = '')
{
    return THEMEPATH .(! isset($path) ? DS .$path : $path);
}

/**
 * [module_path description]
 *
 * @param   [type]  $path  [$path description]
 *
 * @return  [type]         [return description]
 */
function module_path($path = '')
{
    return MODULEPATH .(! isset($path) ? $path : $path);
}

/**
 * Undocumented function
 *
 * @param string $path
 * @return void
 */
function shared_path($path = '')
{
    return SHAREDPATH .(! isset($path) ? $path : $path);
}

/**
 * [storage_path description]
 *
 * @param   [type]  $path  [$path description]
 *
 * @return  [type]         [return description]
 */
function storage_path($path = '')
{
    return STORAGE_PATH .(! isset($path) ? DS .$path : $path);
}

/**
 * [config description]
 *
 * @param   [type]  $key  [$key description]
 *
 * @return  [type]        [return description]
 */
function config($key, $default = null)
{
    return Config::get($key, $default);
}

/**
 * Abort the Application with an HttpException.
 *
 * @param int  code
 * @param string  $message
 * @return string
 */
function abort($code = 404, $message = null)
{
    throw new HttpException($code, $message);
}

/**
 * Class name helper
 * @param string $className
 * @return string
 */
function class_basename($className)
{
    return basename(str_replace('\\', '/', $className));
}

//
// I18N functions

/**
 * Get formatted translated message back.
 * @param string $message English default message
 * @param mixed $args
 * @return string|void
 */
function __($message, $args = null)
{
    if (! $message) {
        return '';
    }

    $params = (func_num_args() === 2) ? (array)$args : array_slice(func_get_args(), 1);

    $language =& Language::get();

    return $language->translate($message, $params);
}

/**
 * Get formatted translated message back with domain.
 * @param string $domain
 * @param string $message
 * @param mixed $args
 * @return string|void
 */
function __d($domain, $message, $args = null)
{
    if (! $message) {
        return '';
    }

    $params = (func_num_args() === 3) ? (array)$args : array_slice(func_get_args(), 2);

    $language =& Language::get($domain);

    return $language->translate($message, $params);
}


/**
 * [array_get description]
 *
 * @param   [type]  $array    [$array description]
 * @param   [type]  $key      [$key description]
 * @param   [type]  $default  [$default description]
 *
 * @return  [type]            [return description]
 */
function array_get($array, $key, $default = null)
{
    if (is_null($key)) {
        return $array;
    } else if (isset($array[$key])) {
        return $array[$key];
    }

    foreach (explode('.', $key) as $segment) {
        if (! is_array($array) || ! array_key_exists($segment, $array)) {
            return $default;
        }

        $array = $array[$segment];
    }

    return $array;
}

/**
 * [array_has description]
 *
 * @param   [type]  $array  [$array description]
 * @param   [type]  $key    [$key description]
 *
 * @return  [type]          [return description]
 */
function array_has($array, $key)
{
    if (empty($array) || is_null($key)) {
        return false;
    } else if (array_key_exists($key, $array)) {
        return true;
    }

    foreach (explode('.', $key) as $segment) {
        if (! is_array($array) || ! array_key_exists($segment, $array)) {
            return false;
        }

        $array = $array[$segment];
    }

    return true;
}

/**
 * [array_set description]
 *
 * @param   [type]  $array  [$array description]
 * @param   [type]  $key    [$key description]
 * @param   [type]  $value  [$value description]
 *
 * @return  [type]          [return description]
 */
function array_set(&$array, $key, $value)
{
    if (is_null($key)) {
        return $array = $value;
    }

    $keys = explode('.', $key);

    while (count($keys) > 1) {
        $key = array_shift($keys);

        if (! isset($array[$key]) || ! is_array($array[$key])) {
            $array[$key] = array();
        }

        $array =& $array[$key];
    }

    $key = array_shift($keys);

    $array[$key] = $value;

    return $array;
}

/**
 * 
 */
if (! function_exists('dd'))
{
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function dd()
    {
        array_map(function ($value)
        {
            with(new Dumper)->dump($value);
        }, func_get_args());
        die (1);
    }
}

/**
 * 
 */
if (! function_exists('vd'))
{
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function vd()
    {
        array_map(function ($value)
        {
            with(new Dumper)->dump($value);
        }, func_get_args());
    }
}

/**
 * 
 */
if (! function_exists('with'))
{
    /**
     * Return the given object. Useful for chaining.
     *
     * @param  mixed  $object
     * @return mixed
     */
    function with($object)
    {
        return $object;
    }
}


// use Npds\Config\Config;
// use Npds\Support\Debug\Dumper;
// use Npds\Exceptions\Http\HttpException;


// /**
//  * Abort the Application with an HttpException.
//  *
//  * @param int  code
//  * @param string  $message
//  * @return string
//  */
// function abort($code = 404, $message = null)
// {
//     throw new HttpException($code, $message);
// }

// /**
//  * Site URL helper
//  *
//  * @param string $path
//  * @return string
//  */
// function site_url($path = '/')
// {
//     return Config::get('app.url') .ltrim($path, '/');
// }


// /**
//  * [array_get description]
//  *
//  * @param   [type]  $array    [$array description]
//  * @param   [type]  $key      [$key description]
//  * @param   [type]  $default  [$default description]
//  *
//  * @return  [type]            [return description]
//  */
// function array_get($array, $key, $default = null)
// {
//     if (is_null($key)) {
//         return $array;
//     } else if (isset($array[$key])) {
//         return $array[$key];
//     }

//     foreach (explode('.', $key) as $segment) {
//         if (! is_array($array) || ! array_key_exists($segment, $array)) {
//             return $default;
//         }

//         $array = $array[$segment];
//     }

//     return $array;
// }

// /**
//  * [array_has description]
//  *
//  * @param   [type]  $array  [$array description]
//  * @param   [type]  $key    [$key description]
//  *
//  * @return  [type]          [return description]
//  */
// function array_has($array, $key)
// {
//     if (empty($array) || is_null($key)) {
//         return false;
//     } else if (array_key_exists($key, $array)) {
//         return true;
//     }

//     foreach (explode('.', $key) as $segment) {
//         if (! is_array($array) || ! array_key_exists($segment, $array)) {
//             return false;
//         }

//         $array = $array[$segment];
//     }

//     return true;
// }

// /**
//  * [array_set description]
//  *
//  * @param   [type]  $array  [$array description]
//  * @param   [type]  $key    [$key description]
//  * @param   [type]  $value  [$value description]
//  *
//  * @return  [type]          [return description]
//  */
// function array_set(&$array, $key, $value)
// {
//     if (is_null($key)) {
//         return $array = $value;
//     }

//     $keys = explode('.', $key);

//     while (count($keys) > 1) {
//         $key = array_shift($keys);

//         if (! isset($array[$key]) || ! is_array($array[$key])) {
//             $array[$key] = array();
//         }

//         $array =& $array[$key];
//     }

//     $key = array_shift($keys);

//     $array[$key] = $value;

//     return $array;
// }

// /**
//  * 
//  */
// if (! function_exists('dd'))
// {
//     /**
//      * Dump the passed variables and end the script.
//      *
//      * @param  mixed
//      * @return void
//      */
//     function dd()
//     {
//         array_map(function ($value)
//         {
//             with(new Dumper)->dump($value);
//         }, func_get_args());
//         die (1);
//     }
// }

// /**
//  * 
//  */
// if (! function_exists('vd'))
// {
//     /**
//      * Dump the passed variables and end the script.
//      *
//      * @param  mixed
//      * @return void
//      */
//     function vd()
//     {
//         array_map(function ($value)
//         {
//             with(new Dumper)->dump($value);
//         }, func_get_args());
//     }
// }

// /**
//  * 
//  */
// if (! function_exists('with'))
// {
//     /**
//      * Return the given object. Useful for chaining.
//      *
//      * @param  mixed  $object
//      * @return mixed
//      */
//     function with($object)
//     {
//         return $object;
//     }
// }