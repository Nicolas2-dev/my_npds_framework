<?php

namespace Npds\ORM\Relation;

use Npds\Database\Connection;
use Npds\Database\Manager as Database;
use Npds\ORM\Model;

use \PDO;

/**
 * Undocumented class
 */
class Pivot extends Model
{
    /**
     * [$parent description]
     *
     * @var [type]
     */
    protected $parent;

    /**
     * [$foreignKey description]
     *
     * @var [type]
     */
    protected $foreignKey;

    /**
     * [$otherKey description]
     *
     * @var [type]
     */
    protected $otherKey;

    /**
     * [$guarded description]
     *
     * @var [type]
     */
    protected $guarded = [];


    /**
     * [__construct description]
     *
     * @param   Model  $parent      [$parent description]
     * @param   array  $attributes  [$attributes description]
     * @param   [type] $table       [$table description]
     * @param   [type] $exists      [$exists description]
     * @param   false               [ description]
     *
     * @return  [type]              [return description]
     */
    public function __construct(Model $parent, array $attributes, $table, $exists = false)
    {
        $this->table = $table;

        // Execute the parent Constructor.
        parent::__construct();

        // Init this pivot Model.
        $this->attributes = $attributes;

        $this->initObject($exists);

        // Setup the parent Model.
        $this->parent = $parent;
    }

    /**
     * [getForeignKey description]
     *
     * @return  [type]  [return description]
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * [getOtherKey description]
     *
     * @return  [type]  [return description]
     */
    public function getOtherKey()
    {
        return $this->otherKey;
    }

    /**
     * [setPivotKeys description]
     *
     * @param   [type]  $foreignKey  [$foreignKey description]
     * @param   [type]  $otherKey    [$otherKey description]
     *
     * @return  [type]               [return description]
     */
    public function setPivotKeys($foreignKey, $otherKey)
    {
        $this->foreignKey = $foreignKey;

        $this->otherKey = $otherKey;

        return $this;
    }

    /**
     * [relatedIds description]
     *
     * @return  [type]  [return description]
     */
    public function relatedIds()
    {
        $otherId = $this->getAttribute($this->otherKey);

        //
        $query = $this->newBaseQuery();

        $data = $query->where($this->otherKey, $otherId)->select($this->foreignKey)->get();

        if($data === null) {
            return false;
        }

        // Parse the gathered data and return the result.
        $result = array();

        foreach($data as $row) {
            $result[] = array_shift($row);
        }

        return $result;
    }
    
}
