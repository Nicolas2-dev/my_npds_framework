<?php

namespace Npds\view;

use Npds\Support\Inflector;
use Npds\Http\Response;

/**
 * Undocumented class
 */
class View
{

    /**
     * [$path description]
     *
     * @var [type]
     */
    protected $path = null;

    /**
     * [$data description]
     *
     * @var [type]
     */
    protected $data = array();

    /**
     * [$shared description]
     *
     * @var [type]
     */
    protected static $shared = array();


    /**
     * [__construct description]
     *
     * @param   [type] $path  [$path description]
     * @param   array  $data  [$data description]
     * @param   array         [ description]
     *
     * @return  [type]        [return description]
     */
    public function __construct($path, array $data = array())
    {
        if (! is_readable($path)) {
            throw new \UnexpectedValueException(__d('system', 'File not found: {0}', $path));
        }

        $this->path = $path;
        $this->data = $data;
    }

    /**
     * [__get description]
     *
     * @param   [type]  $key  [$key description]
     *
     * @return  [type]        [return description]
     */
    public function __get($key)
    {
        return $this->data[$key];
    }

    /**
     * [__set description]
     *
     * @param   [type]  $key    [$key description]
     * @param   [type]  $value  [$value description]
     *
     * @return  [type]          [return description]
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * [__isset description]
     *
     * @param   [type]  $key  [$key description]
     *
     * @return  [type]        [return description]
     */
    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * [__toString description]
     *
     * @return  [type]  [return description]
     */
    public function __toString()
    {
        return $this->fetch();
    }

    /**
     * [__call description]
     *
     * @param   [type]  $method  [$method description]
     * @param   [type]  $params  [$params description]
     *
     * @return  [type]           [return description]
     */
    public function __call($method, $params)
    {
        if (strpos($method, 'with') === 0) {
            $name = Inflector::tableize(substr($method, 4));

            return $this->with($name, array_shift($params));
        }

        throw new \BadMethodCallException(__d('system', 'Method [{0}] is not defined on the View class', $method));
    }

    /**
     * [exists description]
     *
     * @param   [type]  $view  [$view description]
     *
     * @return  [type]         [return description]
     */
    public static function exists($view)
    {
        $path = BASEPATH .str_replace('/', DS, "$view.php");

        return is_readable($path);
    }

    /**
     * [make description]
     *
     * @param   [type] $view  [$view description]
     * @param   array  $data  [$data description]
     * @param   array         [ description]
     *
     * @return  [type]        [return description]
     */
    public static function make($view, array $data = array())
    {
        // Get the Controller instance.
        $controller =& get_instance();           

        $viewsPath = BASEPATH;

        if ($view == $controller->method()) {
            $viewsPath = $controller->viewsPath();
        }

        // Prepare the file path.
        $path = $viewsPath .$view .'.php'; 

        return new View($path, $data);
    }

    /**
     * [layout description]
     *
     * @param   [type] $layout  [$layout description]
     * @param   array  $data    [$data description]
     * @param   array           [ description]
     *
     * @return  [type]          [return description]
     */
    public static function layout($layout = null, array $data = array())
    {
        // Get the Controller instance.
        $controller =& get_instance();

        $template = $controller->template();
        $template_dir = $controller->template_dir();

        if(is_null($layout)) {
            $layout = $controller->layout();
        }

        // Prepare the file path.
        $path = BASEPATH .'Themes' .DS .$template_dir .DS .$template .DS .'Layouts' .DS .$layout .'.php';

        //
        Response::addHeader('Content-Type: text/html; charset=UTF-8');

        return new View($path, $data);
    }

    /**
     * [layout description]
     *
     * @param   [type] $layout  [$layout description]
     * @param   array  $data    [$data description]
     * @param   array           [ description]
     *
     * @return  [type]          [return description]
     */
    public static function module_layout($layout = null, array $data = array())
    {
        // Get the Controller instance.
        $controller =& get_instance();

        if(is_null($layout)) {
            $layout = $controller->layout();
        }

        $module = $controller->module();

        // Prepare the file path.
        $path = BASEPATH . 'Modules' .DS . $module .DS . 'Views' .DS .'Layouts' .DS .$layout .'.php';

        //
        Response::addHeader('Content-Type: text/html; charset=UTF-8');

        return new View($path, $data);
    }

    /**
     * [fragment description]
     *
     * @param   [type] $fragment  [$fragment description]
     * @param   array  $data      [$data description]
     * @param   array             [ description]
     *
     * @return  [type]            [return description]
     */
    public static function fragment($fragment, array $data = array())
    {
        // Get the Controller instance.
        $controller =& get_instance();

        $template = $controller->template();
        $template_dir = $controller->template_dir();

        // Prepare the file path.
        $path = BASEPATH .'Themes' .DS .$template_dir .DS .$template .DS .'Fragments' .DS .$fragment .'.php';

        return new View($path, $data);
    }

    /**
     * [render description]
     *
     * @return  [type]  [return description]
     */
    public function render()
    {
        // Prepare the rendering variables from the internal data.
        foreach ($this->data() as $variable => $value) {
            ${$variable} = $value;
        }

        require $this->path;
    }

    /**
     * [fetch description]
     *
     * @return  [type]  [return description]
     */
    public function fetch()
    {
        ob_start();

        $this->render();

        return ob_get_clean();
    }

    /**
     * [display description]
     *
     * @return  [type]  [return description]
     */
    public function display()
    {
        Response::sendHeaders();

        $this->render();
    }

    /**
     * [data description]
     *
     * @return  [type]  [return description]
     */
    public function data()
    {
        $data = array_merge($this->data, static::$shared);

        // All nested Views are evaluated before the main View.
        foreach ($data as $key => $value) {
            if ($value instanceof View) {
                $data[$key] = $value->fetch();
            }
        }

        return $data;
    }

    /**
     * [nest description]
     *
     * @param   [type] $key   [$key description]
     * @param   [type] $view  [$view description]
     * @param   array  $data  [$data description]
     * @param   array         [ description]
     *
     * @return  [type]        [return description]
     */
    public function nest($key, $view, array $data = array())
    {
        return $this->with($key, static::make($view, $data));
    }

    /**
     * [with description]
     *
     * @param   [type]  $key    [$key description]
     * @param   [type]  $value  [$value description]
     *
     * @return  [type]          [return description]
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * [shares description]
     *
     * @param   [type]  $key    [$key description]
     * @param   [type]  $value  [$value description]
     *
     * @return  [type]          [return description]
     */
    public function shares($key, $value)
    {
        static::share($key, $value);

        return $this;
    }

    /**
     * [share description]
     *
     * @param   [type]  $key    [$key description]
     * @param   [type]  $value  [$value description]
     *
     * @return  [type]          [return description]
     */
    public static function share($key, $value)
    {
        static::$shared[$key] = $value;
    }

}
