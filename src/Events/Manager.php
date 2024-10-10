<?php

namespace Npds\Events;

use Npds\Core\Controller;
use Npds\Events\Event;
use Npds\Events\Listener;

/**
 * Undocumented class
 */
class Manager
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
    private $events = array();

    /**
     * Undocumented variable
     *
     * @var string
     */
    private static $hookPath = 'Npds.Events.Manager.LegacyHook_';

    
    /**
     * [__construct description]
     *
     * @return  [type]  [return description]
     */
    public function __construct()
    {
        self::$instance =& $this;
    }

    /**
     * [getInstance description]
     *
     * @return  [type]  [return description]
     */
    public static function &getInstance()
    {
        if (! self::$instance) {
            $manager = new self();
        } else {
            $manager =& self::$instance;
        }

        return $manager;
    }

    /**
     * [initialize description]
     *
     * @return  [type]  [return description]
     */
    public static function initialize()
    {
        // Get the EventManager instance.
        $manager = self::getInstance();

        $manager->sortListeners();
    }

    /**
     * [addListener description]
     *
     * @param   [type]  $name      [$name description]
     * @param   [type]  $callback  [$callback description]
     * @param   [type]  $priority  [$priority description]
     *
     * @return  [type]             [return description]
     */
    public static function addListener($name, $callback, $priority = 0)
    {
        // Get the EventManager instance.
        $manager = self::getInstance();

        if (empty($name)) {
            throw new \UnexpectedValueException(__d('system', 'The Event Name can not be empty'));
        }

        $manager->attach($name, $callback, $priority);
    }

    /**
     * [hasEvent description]
     *
     * @param   [type]  $name  [$name description]
     *
     * @return  [type]         [return description]
     */
    public static function hasEvent($name)
    {
        // Get the EventManager instance.
        $manager = self::getInstance();

        if (! empty($name)) {
            return $manager->exists($name);
        }

        return false;
    }

    /**
     * [addHook description]
     *
     * @param   [type]  $where     [$where description]
     * @param   [type]  $callback  [$callback description]
     *
     * @return  [type]             [return description]
     */
    public static function addHook($where, $callback)
    {
        // Get the EventManager instance.
        $manager = self::getInstance();

        if (empty($where)) {
            throw new \UnexpectedValueException(__d('system', 'The Hook Name can not be empty'));
        }

        $name = self::$hookPath .$where;

        $manager->attach($name, $callback);
    }

    /**
     * [hasHook description]
     *
     * @param   [type]  $where  [$where description]
     *
     * @return  [type]          [return description]
     */
    public static function hasHook($where)
    {
        // Get the EventManager instance.
        $manager = self::getInstance();

        if (! empty($where)) {
            $name = self::$hookPath .$where;

            return $manager->exists($name);
        }

        return false;
    }

    /**
     * [runHook description]
     *
     * @param   [type]  $where  [$where description]
     * @param   [type]  $args   [$args description]
     *
     * @return  [type]          [return description]
     */
    public static function runHook($where, $args = '')
    {
        // Get the EventManager instance.
        $manager = self::getInstance();

        if (empty($where)) {
            throw new \UnexpectedValueException(__d('system', 'The Hook Name can not be empty'));
        }

        // Prepare the parameters.
        $name = self::$hookPath .$where;

        $result = $args;

        // Get the Listerners registered to this Event.
        $listeners = $manager->listeners($name);

        if ($listeners === null) {
            // There are no Listeners registered for this Event.
            return false;
        }

        // First, preserve a instance of the Current Controller.
        $controller = Controller::getInstance();

        // Execute every Listener Callback, passing Result as parameter.
        foreach ($listeners as $listener) {
            $result = $manager->invokeObject($listener->callback(), $result);
        }

        // Ensure the restoration of the right Controller instance.
        $controller->setInstance();

        return $result;
    }

    /**
     * [sendEvent description]
     *
     * @param   [type] $name    [$name description]
     * @param   [type] $params  [$params description]
     * @param   array  $result  [$result description]
     *
     * @return  [type]          [return description]
     */
    public static function sendEvent($name, $params = array(), &$result = null)
    {
        // Get the EventManager instance.
        $manager = self::getInstance();

        if (empty($name)) {
            throw new \UnexpectedValueException(__d('system', 'The Event Name can not be empty'));
        }

        if (! $manager->exists($name)) {
            // There are no Listeners registered for this Event.
            return false;
        }

        // Create a new Event.
        $event = new Event($name, $params);

        // Deploy the Event to its Listeners and parse the Result from every one.
        return $manager->notify($event, function ($data) use (&$result) {
            if (is_array($result)) {
                $result[] = $data;
            } else if (is_string($result)) {
                if (! is_string($data) && ! is_integer($data)) {
                    throw new \UnexpectedValueException(__d('system', 'Unsupported Data type while the Result is String'));
                }

                $result .= $data;
            } else if (is_bool($result)) {
                if (! is_bool($data)) {
                    throw new \UnexpectedValueException(__d('system', 'Unsupported Data type while the Result is Boolean'));
                }

                $result = $result ? $data : false;
            } else if (! is_null($result)) {
                throw new \UnexpectedValueException(__d('system', 'Unsupported Result type'));
            }
        });
    }

    /**
     * [events description]
     *
     * @return  [type]  [return description]
     */
    public function events()
    {
        return $this->events;
    }

    /**
     * [listeners description]
     *
     * @param   [type]  $name  [$name description]
     *
     * @return  [type]         [return description]
     */
    public function listeners($name)
    {
        if (! empty($name) && isset($this->events[$name])) {
            return $this->events[$name];
        }

        // Let's make Tom happy! ;). Thanks <3
        return null;
    }
 
    /**
     * [exists description]
     *
     * @param   [type]  $name  [$name description]
     *
     * @return  [type]         [return description]
     */
    public function exists($name)
    {
        if (! empty($name)) {
            return isset($this->events[$name]);
        }

        return false;
    }

    /**
     * [attach description]
     *
     * @param   [type]  $name      [$name description]
     * @param   [type]  $callback  [$callback description]
     * @param   [type]  $priority  [$priority description]
     *
     * @return  [type]             [return description]
     */
    public function attach($name, $callback, $priority = 0)
    {
        if (empty($name)) {
            throw new \UnexpectedValueException(__d('system', 'The Event Name can not be empty'));
        }

        if (! array_key_exists($name, $this->events)) {
            $this->events[$name] = array();
        }

        $listeners =& $this->events[$name];

        $listeners[] = new Listener($name, $callback, $priority);
    }

    /**
     * [dettach description]
     *
     * @param   [type]  $name      [$name description]
     * @param   [type]  $callback  [$callback description]
     *
     * @return  [type]             [return description]
     */
    public function dettach($name, $callback)
    {
        if (empty($name) || ! $this->exists($name)) {
            return false;
        }

        $listeners =& $this->events[$name];

        $listeners = array_filter($listeners, function ($listener) use ($callback) {
            return ($listener->callback() !== $callback);
        });

        return true;
    }

    /**
     * [clear description]
     *
     * @param   [type]  $name  [$name description]
     *
     * @return  [type]         [return description]
     */
    public function clear($name = null)
    {
        if ($name !== null) {
            // Is wanted to clear the Listeners from a specific Event.
            unset($this->events[$name]);
        } else {
            // Clear the entire Events list.
            $this->events = array();
        }
    }

    /**
     * [trigger description]
     *
     * @param   [type] $name      [$name description]
     * @param   [type] $params    [$params description]
     * @param   array  $callback  [$callback description]
     *
     * @return  [type]            [return description]
     */
    public function trigger($name, $params = array(), $callback = null)
    {
        if (empty($name)) {
            throw new \UnexpectedValueException(__d('system', 'The Event Name can not be empty'));
        }

        // Create a new Event.
        $event = new Event($name, $params);

        // Deploy the Event notification to Listeners.
        return $this->notify($event, $callback);
    }

    /**
     * [notify description]
     *
     * @param   [type]  $event     [$event description]
     * @param   [type]  $callback  [$callback description]
     *
     * @return  [type]             [return description]
     */
    public function notify($event, $callback = null)
    {
        $name = $event->name();

        if (! $this->exists($name)) {
            // There are no Listeners to observe this type of Event.
            return false;
        }

        // Get the Listerners registered to this Event.
        $listeners = $this->events[$name];

        // First, preserve a instance of the Current Controller.
        $controller = Controller::getInstance();

        // Deploy the Event to every Listener registered.
        foreach ($listeners as $listener) {
            // Invoke the Listener's Callback with the Event as parameter.
            $result = $this->invokeObject($listener->callback(), $event);

            if ($callback) {
                // Invoke the Callback with the Result as parameter.
                $this->invokeCallback($callback, $result);
            }
        }

        // Ensure the restoration of the right Controller instance.
        $controller->setInstance();

        return true;
    }

    /**
     * [invokeObject description]
     *
     * @param   [type]  $callback  [$callback description]
     * @param   [type]  $param     [$param description]
     *
     * @return  [type]             [return description]
     */
    protected function invokeObject($callback, $param)
    {
        if (is_object($callback)) {
            // Call the Closure.
            return call_user_func($callback, $param);
        }

        // Call the object Class and its Method.
        $segments = explode('@', $callback);

        $className = $segments[0];
        $method    = $segments[1];

        // Check first if the Class exists.
        if (!class_exists($className)) {
            throw new \Exception(__d('system', 'Class not found: {0}', $className));
        }

        // Initialize the Class.
        $object = new $className();

        // The called Method should be defined in the called Class, not in one of its parents.
        if (! in_array(strtolower($method), array_map('strtolower', get_class_methods($object)))) {
            throw new \Exception(__d('system', 'Method not found: {0}@{1}', $className, $method));
        }

        if ($object instanceof Controller) {
            // We are going to call-out a Controller; special setup is required.
            // The Controller instance should be properly initialized before executing its Method.
            $object->initialize($method, array($param));
        }

        // Execute the Object's Method and return the result.
        return call_user_func(array($object, $method), $param);
    }

    /**
     * [invokeCallback description]
     *
     * @param   [type]  $callback  [$callback description]
     * @param   [type]  $param     [$param description]
     *
     * @return  [type]             [return description]
     */
    protected function invokeCallback($callback, $param)
    {
        if (is_callable($callback)) {
            // Call the Closure.
            return call_user_func($callback, $param);
        }

        throw new \UnexpectedValueException(__d('system', 'Unsupported Callback type'));
    }

    /**
     * [sortListeners description]
     *
     * @return  [type]  [return description]
     */
    protected function sortListeners()
    {
        $events = array();

        foreach ($this->events as $name => $listeners) {
            usort($listeners, function ($a, $b) {
                return ($a->priority() - $b->priority());
            });

            $events[$name] = $listeners;
        }

        $this->events = $events;
    }
    
}
