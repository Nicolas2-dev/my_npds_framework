<?php

namespace Npds\Events;

/**
 * Undocumented class
 */
class Listener
{
    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $name;

    /**
     * Undocumented variable
     *
     * @var [type]
     */ 
    private $callback;

    /**
     * Undocumented variable
     *
     * @var [type]
     */
    private $priority;


    /**
     * [__construct description]
     *
     * @param   [type]  $name      [$name description]
     * @param   [type]  $callback  [$callback description]
     * @param   [type]  $priority  [$priority description]
     *
     * @return  [type]             [return description]
     */
    public function __construct($name, $callback, $priority = 0)
    {
        $this->name    = $name;
        $this->callback = $callback;
        $this->priority = $priority;
    }

    /**
     * [name description]
     *
     * @return  [type]  [return description]
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * [callback description]
     *
     * @return  [type]  [return description]
     */
    public function callback()
    {
        return $this->callback;
    }

    /**
     * [priority description]
     *
     * @return  [type]  [return description]
     */
    public function priority()
    {
        return $this->priority;
    }
    
}
