<?php

namespace Npds\Events;

/**
 * Undocumented class
 */
class Event
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
    private $params;


    /**
     * [__construct description]
     *
     * @param   [type] $name    [$name description]
     * @param   [type] $params  [$params description]
     * @param   array           [ description]
     *
     * @return  [type]          [return description]
     */
    public function __construct($name, $params = array())
    {
        $this->name   = $name;
        $this->params = $params;
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
     * [params description]
     *
     * @return  [type]  [return description]
     */
    public function params()
    {
        return $this->params;
    }
    
}
