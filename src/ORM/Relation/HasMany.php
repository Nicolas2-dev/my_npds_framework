<?php

namespace Npds\ORM\Relation;

use Npds\ORM\Model;
use Npds\ORM\Relation;

/**
 * Undocumented class
 */
class HasMany extends Relation
{
    /**
     * [$foreignKey description]
     *
     * @var [type]
     */
    protected $foreignKey;


    /**
     * [__construct description]
     *
     * @param   [type] $className   [$className description]
     * @param   Model  $model       [$model description]
     * @param   [type] $foreignKey  [$foreignKey description]
     *
     * @return  [type]              [return description]
     */
    public function __construct($className, Model $model, $foreignKey = null)
    {
        parent::__construct($className, $model);

        // The foreignKey is associated to host Model.
        if($foreignKey === null) {
            $this->foreignKey = $model->getForeignKey();
        } else {
            $this->foreignKey = $foreignKey;
        }
    }

    /**
     * [type description]
     *
     * @return  [type]  [return description]
     */
    public function type()
    {
        return 'hasMany';
    }

    /**
     * [get description]
     *
     * @return  [type]  [return description]
     */
    public function get()
    {
        $id = $this->parent->getKey();

        $query = $this->query->getBaseQuery();

        //
        $data = $query->where($this->foreignKey, $id)->get();

        //
        $this->query = $this->related->newBuilder();

        if($data === null) {
            return false;
        }
        
        //
        $key = $this->related->getKeyName();

        $result = array();

        foreach ($data as $row) {
            $id = $row[$key];

            $result[$id] = $this->related->newFromBuilder($row);
        }

        return $result;
    }
    
}
