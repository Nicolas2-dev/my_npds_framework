<?php

namespace Npds\Database\Query;

/**
 * Undocumented class
 */
class Raw
{

    /**
     * [$value description]
     *
     * @var [type]
     */
    protected $value;

    /**
     * [$bindings description]
     *
     * @var [type]
     */
    protected $bindings;

    
    /**
     * [__construct description]
     *
     * @param   [type] $value     [$value description]
     * @param   [type] $bindings  [$bindings description]
     * @param   array             [ description]
     *
     * @return  [type]            [return description]
     */
    public function __construct($value, $bindings = array())
    {
        $this->value = (string) $value;

        $this->bindings = (array) $bindings;
    }

    /**
     * [getBindings description]
     *
     * @return  [type]  [return description]
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * [__toString description]
     *
     * @return  [type]  [return description]
     */
    public function __toString()
    {
        return (string) $this->value;
    }
    
}
