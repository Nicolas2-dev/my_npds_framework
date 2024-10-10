<?php

namespace Npds\ORM\Relation;

use Npds\ORM\Model;
use Npds\ORM\Relation;

/**
 * Undocumented class
 */
class BelongsTo extends Relation
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

        // Process the foreignKey.
        if($foreignKey === null) {
            $this->foreignKey = $this->related->getForeignKey();
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
        return 'belongsTo';
    }

    /**
     * [get description]
     *
     * @return  [type]  [return description]
     */
    public function get()
    {
        $id = $this->parent->getAttribute($this->foreignKey);

        return $this->query->findBy($this->related->getKeyName(), $id);
    }

}
