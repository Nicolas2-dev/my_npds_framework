<?php

namespace Npds\Routing;

use Npds\view\View;
use Npds\Routing\Url;
use Npds\Http\Request;
use Npds\Config\Config;
use Npds\Http\Response;
use Npds\Routing\Route;
use Npds\Core\Controller;

/**
 * Undocumented class
 */
class Router
{

    /**
     * [$instance description]
     *
     * @var [type]
     */
    private static $instance;

    /**
     * [$routeGroups description]
     *
     * @var [type]
     */
    private static $routeGroups = array();

    /**
     * [$routes description]
     *
     * @var [type]
     */
    protected $routes = array();

    /**
     * [$defaultRoute description]
     *
     * @var [type]
     */
    private $defaultRoute = null;

    /**
     * [$matchedRoute description]
     *
     * @var [type]
     */
    protected $matchedRoute = null;

    /**
     * [$errorCallback description]
     *
     * @var [type]
     */
    private $errorCallback = '\App\Controllers\Error@error404';

    /**
     * [$config description]
     *
     * @var [type]
     */
    private $config;

    /**
     * [$methods description]
     *
     * @var [type]
     */
    public static $methods = array('GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS');


    /**
     * [__construct description]
     *
     * @return  [type]  [return description]
     */
    public function __construct()
    {
        self::$instance =& $this;

        $this->config = Config::get('routing');
    }

    /**
     * [getInstance description]
     *
     * @return  [type]  [return description]
     */
    public static function &getInstance()
    {
        $appRouter = '\Npds\Routing\Router';

        if (! self::$instance) {
            $router = new $appRouter();
        } else {
            $router =& self::$instance;
        }

        return $router;
    }

    /**
     * [__callStatic description]
     *
     * @param   [type]  $method  [$method description]
     * @param   [type]  $params  [$params description]
     *
     * @return  [type]           [return description]
     */
    public static function __callStatic($method, $params)
    {
        $method = strtoupper($method);

        if(($method == 'ANY') || in_array($method, static::$methods)) {
            $route    = array_shift($params);
            $callback = array_shift($params);

            // Register the route.
            static::register($method, $route, $callback);
        }
    }

    /**
     * [routes description]
     *
     * @return  [type]  [return description]
     */
    public function routes()
    {
        return $this->routes;
    }

    /**
     * [error description]
     *
     * @param   [type]  $callback  [$callback description]
     *
     * @return  [type]             [return description]
     */
    public static function error($callback)
    {
        // Get the Router instance.
        $router = self::getInstance();

        $router->callback($callback);
    }

    /**
     * [catchAll description]
     *
     * @param   [type]  $callback  [$callback description]
     *
     * @return  [type]             [return description]
     */
    public static function catchAll($callback)
    {
        // Get the Router instance.
        $router =& self::getInstance();

        //
        $router->defaultRoute = new Route(static::$methods, '(:all)', $callback);
    }

    /**
     * [match description]
     *
     * @param   [type]  $method    [$method description]
     * @param   [type]  $route     [$route description]
     * @param   [type]  $callback  [$callback description]
     *
     * @return  [type]             [return description]
     */
    public static function match($method, $route, $callback = null)
    {
        self::register($method, $route, $callback);
    }

    /**
     * [share description]
     *
     * @param   [type]  $routes    [$routes description]
     * @param   [type]  $callback  [$callback description]
     *
     * @return  [type]             [return description]
     */
    public static function share($routes, $callback)
    {
        foreach ($routes as $entry) {
            $method = array_shift($entry);
            $route  = array_shift($entry);

            // Register the route.
            static::register($method, $route, $callback);
        }
    }

    /**
     * [group description]
     *
     * @param   [type]  $group     [$group description]
     * @param   [type]  $callback  [$callback description]
     *
     * @return  [type]             [return description]
     */
    public static function group($group, $callback)
    {
        if(is_array($group)) {
            $prefix    = trim($group['prefix'], '/');
            $namespace = isset($group['namespace']) ? trim($group['namespace'], '\\') : '';
        } else {
            $prefix    = trim($group, '/');
            $namespace = '';
        }

        // Add the current Route Group to the array.
        array_push(self::$routeGroups, array('prefix' => $prefix, 'namespace' => $namespace));

        // Call the Callback, to define the Routes on the current Group.
        call_user_func($callback);

        // Removes the last Route Group from the array.
        array_pop(self::$routeGroups);
    }

    /* The Resourcefull Routes in the Laravel Style.

    Method     |  Path                 |  Action   |
    -----------|-----------------------|-----------|
    GET        |  /photo               |  index    |
    GET        |  /photo/create        |  create   |
    POST       |  /photo               |  store    |
    GET        |  /photo/{photo}       |  show     |
    GET        |  /photo/{photo}/edit  |  edit     |
    PUT/PATCH  |  /photo/{photo}       |  update   |
    DELETE     |  /photo/{photo}       |  destroy  |

    */

    /**
     * [resource description]
     *
     * @param   [type]  $basePath    [$basePath description]
     * @param   [type]  $controller  [$controller description]
     *
     * @return  [type]               [return description]
     */
    public static function resource($basePath, $controller)
    {
        $router =& self::getInstance();

        self::register('get',                 $basePath,                 $controller .'@index');
        self::register('get',                 $basePath .'/create',      $controller .'@create');
        self::register('post',                $basePath,                 $controller .'@store');
        self::register('get',                 $basePath .'/(:any)',      $controller .'@show');
        self::register('get',                 $basePath .'/(:any)/edit', $controller .'@edit');
        self::register(array('put', 'patch'), $basePath .'/(:any)',      $controller .'@update');
        self::register('delete',              $basePath .'/(:any)',      $controller .'@delete');
    }

    /**
     * [callback description]
     *
     * @param   [type]  $callback  [$callback description]
     *
     * @return  [type]             [return description]
     */
    public function callback($callback = null)
    {
        if (is_null($callback)) {
            return $this->errorCallback;
        }

        $this->errorCallback = $callback;

        return null;
    }

    /**
     * [register description]
     *
     * @param   [type]  $method    [$method description]
     * @param   [type]  $route     [$route description]
     * @param   [type]  $callback  [$callback description]
     *
     * @return  [type]             [return description]
     */
    public static function register($method, $route, $callback = null)
    {
        // Get the Router instance.
        $router =& self::getInstance();

        // Prepare the route Methods.
        if(is_string($method) && (strtolower($method) == 'any')) {
            $methods = static::$methods;
        } else {
            $methods = array_map('strtoupper', is_array($method) ? $method : array($method));

            // Ensure the requested Methods being valid ones.
            $methods = array_intersect($methods, static::$methods);
        }

        if (empty($methods)) {
            // If there are no valid Methods defined, fallback to ANY.
            $methods = static::$methods;
        }

        // Prepare the Route PATTERN.
        $pattern = ltrim($route, '/');

        // If $callback is an options array, extract the Filters and Callback.
        if(is_array($callback)) {
            $filters = isset($callback['filters']) ? trim($callback['filters'], '|') : '';

            $callback = isset($callback['uses']) ? $callback['uses'] : null;
        } else {
            $filters = '';
        }

        if (! empty(self::$routeGroups)) {
            $parts = array();

            // The current Controller namespace; prepended to Callback if it is not a Closure.
            $namespace = '';

            foreach (self::$routeGroups as $group) {
                // Add the current prefix to the prefixes list.
                array_push($parts, trim($group['prefix'], '/'));

                // Update always to the last Controller namespace.
                $namespace = trim($group['namespace'], '\\');
            }

            if (! empty($pattern)) {
                array_push($parts, $pattern);
            }

            // Adjust the Route PATTERN.
            if (! empty($parts)) {
                $pattern = implode('/', $parts);
            }

            // Adjust the Route CALLBACK, when it is not a Closure.
            if(! empty($namespace) && ! is_object($callback)) {
                $callback = sprintf('%s\%s', $namespace,  trim($callback, '\\'));
            }
        }

        // Create a Route instance using the processed information.
        $route = new Route($methods, $pattern, array('filters' => $filters, 'uses' => $callback));

        // Add the current Route instance to the Router's known Routes list.
        array_push($router->routes, $route);
    }

    /**
     * [matchedRoute description]
     *
     * @return  [type]  [return description]
     */
    public function matchedRoute()
    {
        return $this->matchedRoute;
    }

    /**
     * [invokeController description]
     *
     * @param   [type]  $className  [$className description]
     * @param   [type]  $method     [$method description]
     * @param   [type]  $params     [$params description]
     *
     * @return  [type]              [return description]
     */
    protected function invokeController($className, $method, $params)
    {
        // The Controller's Execution Flow cannot be called via Router.
        if(($method == 'initialize') || ($method == 'execute')) {
            return false;
        }

        // Initialize the Controller.
        /** @var Controller $controller */
        $controller = new $className();

        // Obtain the available methods into requested Controller.
        $methods = array_map('strtolower', get_class_methods($controller));

        // The called Method should be defined right on the called Controller to be executed.
        if (in_array(strtolower($method), $methods)) {
            // Start the Execution Flow and return the result.
            return $controller->execute($method, $params);
        }

        return false;
    }

    /**
     * [invokeObject description]
     *
     * @param   [type] $callback  [$callback description]
     * @param   [type] $params    [$params description]
     * @param   array             [ description]
     *
     * @return  [type]            [return description]
     */
    protected function invokeObject($callback, $params = array())
    {
        if (is_object($callback)) {
            // Call the Closure function with the given arguments.
             $result = call_user_func_array($callback, $params);

             if ($result instanceof View) {
                 // If the object invocation returned a View instance, render it.
                 $result->display();
             }

             return true;
        }

        // Call the object Controller and its Method.
        $segments = explode('@', $callback);

        $controller = $segments[0];
        $method     = $segments[1];

        // The Method shouldn't start with '_'; also check if the Controller's class exists.
        if (($method[0] !== '_') && class_exists($controller)) {
            // Invoke the Controller's Method with the given arguments.
            return $this->invokeController($controller, $method, $params);
        }

        return false;
    }

    /**
     * [dispatch description]
     *
     * @return  [type]  [return description]
     */
    public function dispatch()
    {
        $patterns = $this->config('patterns');

        // Detect the current URI.
        $uri = Url::detectUri();

        // First, we will supose that URI is associated with an Asset File.
        if (Request::isGet() && $this->dispatchFile($uri)) {
            return true;
        }

        // Not an Asset File URI? Routes the current request.
        $method = Request::getMethod();

        // If there exists a Catch-All Route, firstly we add it to Routes list.
        if ($this->defaultRoute !== null) {
            array_push($this->routes, $this->defaultRoute);
        }

        foreach ($this->routes as $route) {
            if ($route->match($uri, $method, $patterns)) {
                // Found a valid Route; process it.
                $this->matchedRoute = $route;

                // Apply the (specified) Filters on matched Route.
                $result = $route->applyFilters();

                if($result === false) {
                    // Matched Route filtering failed; we should go to (404) Error.
                    break;
                }

                $callback = $route->callback();

                if ($callback !== null) {
                    // Invoke the Route's Callback with the associated parameters.
                    return $this->invokeObject($callback, $route->params());
                }

                return true;
            }
        }

        // No valid Route found; invoke the Error Callback with the current URI as parameter.
        $params = array(
            htmlspecialchars($uri, ENT_COMPAT, 'ISO-8859-1', true)
        );

        $this->invokeObject($this->callback(), $params);

        return false;
    }

    /**
     * [dispatchFile description]
     *
     * @param   [type]  $uri  [$uri description]
     *
     * @return  [type]        [return description]
     */
    protected function dispatchFile($uri)
    {
        // For properly Assets serving, the file URI should be as following:
        //
        // /themes/default/assets/css/style.css
        // /modules/blog/assets/css/style.css
        // /assets/css/style.css

        $filePath = '';

        if (preg_match('#^assets/(.*)$#i', $uri, $matches)) {
            $filePath = BASEPATH.'assets'.DS.$matches[1];

        } else if (preg_match('#^(modules)/(.+)/assets/(.*)$#i', $uri, $matches)) {
            // We need to classify the path name (the Module/Theme path).
            //$basePath = ucfirst($matches[1]) .DS .Inflector::classify($matches[2]);
            $basePath = ucfirst($matches[1]) .DS .$matches[2];

            $filePath = BASEPATH.$basePath.DS.'Assets'.DS.$matches[3];

        } else if (preg_match('#^(modules)/(.+)/storage/(.*)$#i', $uri, $matches)) {
            // We need to classify the path name (the Module/Theme path).
            //$basePath = ucfirst($matches[1]) .DS .Inflector::classify($matches[2]);
            $basePath = ucfirst($matches[1]) .DS .$matches[2];

            $filePath = BASEPATH.$basePath.DS.'storage'.DS.$matches[3];

        } else if (preg_match('#^(themes|shared)/(.+)/assets/(.*)$#i', $uri, $matches)) {
            // We need to classify the path name (the Module/Theme path).
            //$basePath = ucfirst($matches[1]) .DS .Inflector::classify($matches[2]);
            $basePath = ucfirst($matches[1]) .DS .$matches[2];

            $filePath = BASEPATH.$basePath.DS.'Assets'.DS.$matches[3];
        }

        if (! empty($filePath)) {
            // Serve the specified Asset File.
            Response::serveFile($filePath);

            return true;
        }

        return false;
    }

    /**
     * [config description]
     *
     * @param   [type]  $key  [$key description]
     *
     * @return  [type]        [return description]
     */
    protected function config($key = null)
    {
        if ($key !== null) {
            return array_key_exists($key, $this->config) ? $this->config[$key] : null;
        }

        return $this->config;
    }
    
}
