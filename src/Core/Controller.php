<?php

namespace Npds\Core;

use Npds\view\View;
use Npds\Http\Response;

/**
 * Undocumented class
 */
abstract class Controller
{
    
    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private static $instance;

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected $data = array();

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    protected $module = null;

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected $params = array();

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    protected $method;

    /**
     * Undocumented variable
     *
     * @var [type]
     */ 
    protected $className;

    /**
     * Undocumented variable
     *
     * @var [type]
     */ 
    protected $viewsPath;

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $template = 'Default';

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $template_dir = 'Frontend';

    /**
     * Undocumented variable
     *
     * @var string
     */
    protected $layout   = 'default';

    /**
     * Undocumented variable
     *
     * @var boolean
     */
    protected $autoRender = true;

    /**
     * Undocumented variable
     *
     * @var boolean
     */
    protected $useLayout  = false;


    /**
     * Undocumented function
     */
    public function __construct()
    {
        self::$instance =& $this;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public static function &getInstance()
    {
        return self::$instance;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function setInstance()
    {
        self::$instance =& $this;
    }

    /**
     * Undocumented function
     *
     * @param [type] $method
     * @param array $params
     * @return void
     */
    public function initialize($method, $params = array())
    {
        // Setup the Controller's properties.
        $this->className = get_class($this);

        $this->method = $method;
        $this->params = $params;

        // Prepare the Views Path using the Controller's full Name including its namespace.
        $classPath = str_replace('\\', '/', ltrim($this->className, '\\'));

        // First, check on the App path.
        if (preg_match('#^App/Controllers/(.*)$#i', $classPath, $matches)) {
            $viewsPath = APPPATH . str_replace('/', DS, 'Views/'.$matches[1]);

        // Secondly, check on the Modules path.
        } else if (preg_match('#^Modules/(.+)/Controllers/(.*)$#i', $classPath, $matches)) {
            $this->module = $matches[1];

            // The View paths are in Module sub-directories.
            $viewsPath = BASEPATH . str_replace('/', DS, 'Modules/'.$matches[1].'/Views/'.$matches[2]);
        } else {
            throw new \Exception(__d('system', 'Unknown Views Path for the Class: {0}', $this->className));
        }

        $this->viewsPath = $viewsPath .DS;
    }

    /**
     * Undocumented function
     *
     * @param [type] $method
     * @param array $params
     * @return bool
     */
    public function execute($method, $params = array())
    {
        // Initialize the Controller instance.
        $this->initialize($method, $params);

        // Before Action stage.
        if ($this->before() === false) {
            // Is wanted to stop the Flight.
            return false;
        }

        // Calling Action stage; execute the Controller's Method with the given arguments.
        $result = call_user_func_array(array($this, $this->method()), $this->params());

        // After Action stage.
        $this->after($result);

        return true;
    }

    /**
     * Undocumented function
     *
     * @return bool
     */
    protected function before()
    {
        return true;
    }

    /**
     * Undocumented function
     *
     * @param [type] $result
     * @return void
     */
    protected function after($result)
    {
        if (is_null($result) || ! $this->autoRender) {
            // No result returned or there is no auto-rendering.
            return true;
        }

        if ($result instanceof View) {
            // The result is a View instance; we should fetch it.
            Response::addHeader('Content-Type: text/html; charset=UTF-8');

            $result = $result->fetch();
        } else if (is_array($result)) {
            // The returned result is an Array; prepare a JSON response.
            Response::addHeader('Content-Type: application/json');

            $result = json_encode($result);
        }

        // Output the result.
        Response::sendHeaders();

        echo $result;

        return true;
    }

    /**
     * Undocumented function
     *
     * @param [type] $value
     * @return void
     */
    protected function autoRender($value = null)
    {
        if (is_null($value)) {
            return $this->autoRender;
        }

        $this->autoRender = $value;
    }

    /**
     * Undocumented function
     *
     * @param [type] $value
     * @return void
     */
    protected function useLayout($value = null)
    {
        if (is_null($value)) {
            return $this->useLayout;
        }

        $this->useLayout = $value;
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @return array|null
     */
    public function data($name = null)
    {
        if (is_null($name)) {
            return $this->data;
        } else if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return null;
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @param [type] $value
     * @return void
     */
    protected function set($name, $value = null)
    {
        if (is_array($name)) {
            if (is_array($value)) {
                $data = array_combine($name, $value);
            } else {
                $data = $name;
            }
        } else {
            $data = array($name => $value);
        }

        $this->data = array_merge($this->data, $data);
    }

    /**
     * Undocumented function
     *
     * @param [type] $title
     * @return void
     */
    protected function title($title)
    {
        $data = array('title' => $title);

        $this->data = array_merge($this->data, $data);

        // Activate the Rendering on Layout.
        $this->useLayout = true;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function module()
    {
        return $this->module;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function params()
    {
        return $this->params;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function viewsPath()
    {
        return $this->viewsPath;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function template()
    {
        return $this->template;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function template_dir()
    {
        return $this->template_dir;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public function layout()
    {
        return $this->layout;
    }

}
