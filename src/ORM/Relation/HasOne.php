<?php

namespace Npds\ORM\Relation;

use Npds\ORM\Model;
use Npds\ORM\Relation;

/**
 * Undocumented class
 */
class HasOne extends Relation
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
        return 'hasOne';
    }

    /**
     * [get description]
     *
     * @return  [type]  [return description]
     */
    public function get()
    {
        $id = $this->parent->getKey();

        return $this->query->findBy($this->foreignKey, $id);
    }
    
}
