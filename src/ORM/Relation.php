<?php

namespace Npds\ORM;

use Npds\ORM\Model;

/**
 * Undocumented class
 */
abstract class Relation
{
    
    /**
     * [$className description]
     *
     * @var [type]
     */
    protected $className;

    /**
     * [$related description]
     *
     * @var [type]
     */
    protected $related;

    /**
     * [$parent description]
     *
     * @var [type]
     */
    protected $parent;

    /**
     * [$query description]
     *
     * @var [type]
     */
    protected $query;

    /**
     * [$passthru description]
     *
     * @var [type]
     */
    protected $passthru = array(
        'find',
        'findBy',
        'findMany',
        'findAll',
        'first',
        'insert',
        'insertIgnore',
        'replace',
        'update',
        'updateBy',
        'updateOrInsert',
        'delete',
        'deleteBy',
        'count',
        'countBy',
        'countAll',
        'isUnique',
        'query',
        'addTablePrefix'
    );


    /**
     * [__construct description]
     *
     * @param   [type] $className  [$className description]
     * @param   Model  $parent     [$parent description]
     *
     * @return  [type]             [return description]
     */
    public function __construct($className, Model $parent)
    {
        $className = sprintf('\\%s', ltrim($className, '\\'));

        if(! class_exists($className)) {
            throw new \Exception(__d('system', 'No valid Class is given: {0}', $className));
        }

        //
        $this->className = $className;

        // Setup the instance of Target Model.
        $this->related = new $className();

        // Setup the Parent Model
        $this->parent = $parent;

        // Setup the Query Builder
        $this->query = $this->related->newBuilder();
    }

    /**
     * [__call description]
     *
     * @param   [type]  $method      [$method description]
     * @param   [type]  $parameters  [$parameters description]
     *
     * @return  [type]               [return description]
     */
    public function __call($method, $parameters)
    {
        $result = call_user_func_array(array($this->query, $method), $parameters);

        return in_array($method, $this->passthru) ? $result : $this;
    }

    /**
     * [getClass description]
     *
     * @return  [type]  [return description]
     */
    public function getClass()
    {
        return $this->className;
    }

    /**
     * [get description]
     *
     * @return  [type]  [return description]
     */
    abstract public function get();

    /**
     * [getBuilder description]
     *
     * @return  [type]  [return description]
     */
    public function getBuilder()
    {
        return $this->query;
    }

    /**
     * [getParent description]
     *
     * @return  [type]  [return description]
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * [getRelated description]
     *
     * @return  [type]  [return description]
     */
    public function getRelated()
    {
        return $this->related;
    }
    
}
