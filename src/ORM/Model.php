<?php

namespace Npds\ORM;

use Npds\Support\Inflector;
use Npds\Database\Connection;
use Npds\Database\Manager as Database;
use Npds\ORM\Builder;
use Npds\ORM\Relation\HasOne;
use Npds\ORM\Relation\HasMany;
use Npds\ORM\Relation\BelongsTo;
use Npds\ORM\Relation\BelongsToMany;
use Npds\ORM\Relation\Pivot;

use PDO;

/**
 * Undocumented class
 */
class Model
{
    
    /**
     * [$className description]
     *
     * @var [type]
     */
    protected $className;

    /**
     * [$exists description]
     *
     * @var [type]
     */
    protected $exists = false;

    /**
     * [$connection description]
     *
     * @var [type]
     */
    protected $connection = 'default';

    /**
     * [$primaryKey description]
     *
     * @var [type]
     */
    protected $primaryKey = 'id';

    /**
     * [$fields description]
     *
     * @var [type]
     */
    protected $fields = array();

    /**
     * [$attributes description]
     *
     * @var [type]
     */
    protected $attributes = array();

    /**
     * [$original description]
     *
     * @var [type]
     */
    protected $original = array();

    /**
     * [$table description]
     *
     * @var [type]
     */
    protected $table;

    /**
     * [$dateFormat description]
     *
     * @var [type]
     */
    protected $dateFormat = 'datetime';

    /**
     * [$timestamps description]
     *
     * @var [type]
     */
    protected $timestamps = false;

    /**
     * [$createdField description]
     *
     * @var [type]
     */
    protected $createdField = 'created_at';

    /**
     * [$updatedField description]
     *
     * @var [type]
     */
    protected $updatedField = 'updated_at';

    /**
     * [$relations description]
     *
     * @var [type]
     */
    protected $relations = array();

    /**
     * [$protectedFields description]
     *
     * @var [type]
     */
    protected $protectedFields = array();

    /**
     * [$serialize description]
     *
     * @var [type]
     */
    protected $serialize = array();

    /**
     * [__construct description]
     *
     * @param   [type]   $connection  [$connection description]
     * @param   default               [ description]
     *
     * @return  [type]                [return description]
     */
    public function __construct($connection = 'default')
    {
        $this->className = get_class($this);

        // Setup the Connection name.
        $this->connection = $connection;

        // Prepare the Table Name only if it is not already specified.
        if (empty($this->table)) {
            // Get the Class name without namespace part.
            $className = class_basename($this->className);

            // Explode the tableized className into segments delimited by '_'.
            $segments = explode('_', Inflector::tableize($className));

            // Replace the last segment with its pluralized variant.
            array_push($segments, Inflector::pluralize(array_pop($segments)));

            // Finally, we recombine the segments, obtaining something like:
            // 'UserProfile' -> 'user_profiles'
            $this->table = implode('_', $segments);
        }

        // Adjust the Relations array to permit the storage of associated Models data.
        if(! empty($this->relations)) {
            $this->relations = array_fill_keys($this->relations, null);
        }

        // Init the Model; exists when it has attributes loaded (via class fetching).
        if(! empty($this->attributes)) {
            $this->initObject(true);
        }
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
     * [getFields description]
     *
     * @return  [type]  [return description]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * [setTable description]
     *
     * @param   [type]  $table  [$table description]
     *
     * @return  [type]          [return description]
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * [getTable description]
     *
     * @return  [type]  [return description]
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * [table description]
     *
     * @return  [type]  [return description]
     */
    public function table()
    {
        return DB_PREFIX .$this->table;
    }

    /**
     * [setConnection description]
     *
     * @param   [type]  $connection  [$connection description]
     *
     * @return  [type]               [return description]
     */
    public function setConnection($connection)
    {
        if($connection !== null) {
            $this->connection = $connection;
        }

        return $this;
    }

    /**
     * [getConnection description]
     *
     * @return  [type]  [return description]
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * [initObject description]
     *
     * @param   [type] $exists  [$exists description]
     * @param   false           [ description]
     *
     * @return  [type]          [return description]
     */
    protected function initObject($exists = false)
    {
        $this->exists = $exists;

        if($this->exist) {
            // Unserialize the specified fields into 'serialize'.
            foreach ($this->serialize as $fieldName) {
                if (! array_key_exists($fieldName, $this->attributes)) {
                    continue;
                }

                $fieldValue = $this->attributes[$fieldName];

                if(! empty($fieldValue)) {
                    $this->attributes[$fieldName] = unserialize($fieldValue);
                }
            }

            // Sync the original attributes.
            $this->syncOriginal();
        }

        $this->afterLoad();
    }

    /**
     * [fill description]
     *
     * @param   array  $attributes  [$attributes description]
     *
     * @return  [type]              [return description]
     */
    public function fill(array $attributes)
    {
        // Skip any protected attributes; the primaryKey is skipped by default.
        $skippedFields = array_merge(
            array($this->primaryKey, $this->createdField, $this->modifiedField),
            $this->protectedFields
        );

        foreach ($attributes as $key => $value) {
            if(! in_array($key, $skippedFields)) {
                $this->setAttribute($key, $value);
            }
        }
    }

    /**
     * [forceFill description]
     *
     * @param   array  $attributes  [$attributes description]
     *
     * @return  [type]              [return description]
     */
    public function forceFill(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * [create description]
     *
     * @param   array  $attributes  [$attributes description]
     * @param   array               [ description]
     *
     * @return  [type]              [return description]
     */
    public static function create(array $attributes = array())
    {
        $model = new static();

        $model->setRawAttributes($attributes);

        // Initialize the Model.
        $model->initObject();

        $model->save();

        return $model;
    }

    /**
      * [hydrate description]
      *
      * @param   array  $items       [$items description]
      * @param   [type] $connection  [$connection description]
      *
      * @return  [type]              [return description]
      */
    public static function hydrate(array $items, $connection = null)
    {
        $instance = new static();

        if($connection !== null) {
            $instance->setConnection($connection);
        }

        $models = array_map(function ($item) use ($instance) {
            return $instance->newFromBuilder($item);
        }, $items);

        return $models;
    }

    /**
     * [newInstance description]
     *
     * @param   [type] $attributes  [$attributes description]
     * @param   array  $exists      [$exists description]
     *
     * @return  [type]              [return description]
     */
    public function newInstance($attributes = array(), $exists = false)
    {
        $instance = new static();

        $instance->setAttributes((array) $attributes);

        // Initialize the Model.
        $instance->initObject($exists);

        return $instance;
    }

    /**
     * [newFromBuilder description]
     *
     * @param   [type] $attributes  [$attributes description]
     * @param   array  $connection  [$connection description]
     *
     * @return  [type]              [return description]
     */
    public function newFromBuilder($attributes = array(), $connection = null)
    {
        $model = $this->newInstance(array(), true);

        $model->setRawAttributes((array) $attributes, true);

        $model->setConnection($connection ?: $this->connection);

        return $model;
    }

    /**
     * [getKey description]
     *
     * @return  [type]  [return description]
     */
    public function getKey()
    {
        return $this->getAttribute($this->primaryKey);
    }

    /**
     * [getKeyName description]
     *
     * @return  [type]  [return description]
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * [getForeignKey description]
     *
     * @return  [type]  [return description]
     */
    public function getForeignKey()
    {
        $tableKey = Inflector::singularize($this->table);

        return $tableKey .'_id';
    }

    /**
     * [toArray description]
     *
     * @param   [type]$withRelations  [$withRelations description]
     * @param   true                  [ description]
     *
     * @return  [type]                [return description]
     */
    public function toArray($withRelations = true)
    {
        if(! $withRelations) {
            return $this->attributes;
        }

        $attributes = $this->attributes;

        foreach ($this->relations as $key => $value) {
            if ($value instanceof Model) {
                // We have an associated Model.
                $attributes[$key] = $value->toArray(false);
            } else if (is_array($value)) {
                // We have an array of associated Models.
                $attributes[$key] = array();

                foreach ($value as $id => $model) {
                    $attributes[$key][$id] = $model->toArray(false);
                }
            } else if (is_null($value)) {
                // We have an empty relationship.
                $attributes[$key] = $value;
            }
        }

        return $attributes;
    }

    /**
     * [setAttributes description]
     *
     * @param   [type]  $attributes  [$attributes description]
     *
     * @return  [type]               [return description]
     */
    public function setAttributes($attributes)
    {
        $this->forceFill($attributes);
    }

    /**
     * [getAttributes description]
     *
     * @return  [type]  [return description]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * [setAttribute description]
     *
     * @param   [type]  $name   [$name description]
     * @param   [type]  $value  [$value description]
     *
     * @return  [type]          [return description]
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * [setRawAttributes description]
     *
     * @param   array  $attributes  [$attributes description]
     * @param   [type] $sync        [$sync description]
     * @param   false               [ description]
     *
     * @return  [type]              [return description]
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        $this->attributes = $attributes;

        if ($sync) {
            $this->syncOriginal();
        }

        return $this;
    }

    /**
     * [getAttribute description]
     *
     * @param   [type]  $name  [$name description]
     *
     * @return  [type]         [return description]
     */
    public function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    /**
     * [getOriginal description]
     *
     * @param   [type]  $key      [$key description]
     * @param   [type]  $default  [$default description]
     *
     * @return  [type]            [return description]
     */
    public function getOriginal($key = null, $default = null)
    {
        if($key === null) {
            return $this->original;
        }

        return array_key_exists($key, $this->original) ? $this->original[$key] : $default;
    }

    /**
     * [syncOriginal description]
     *
     * @return  [type]  [return description]
     */
    public function syncOriginal()
    {
        $this->original = $this->attributes;

        return $this;
    }

    /**
     * [syncOriginalAttribute description]
     *
     * @param   [type]  $attribute  [$attribute description]
     *
     * @return  [type]              [return description]
     */
    public function syncOriginalAttribute($attribute)
    {
        $this->original[$attribute] = $this->attributes[$attribute];

        return $this;
    }

    /**
     * [isDirty description]
     *
     * @param   [type]  $attributes  [$attributes description]
     *
     * @return  [type]               [return description]
     */
    public function isDirty($attributes = null)
    {
        $dirty = $this->getDirty();

        if (is_null($attributes)) {
            return count($dirty) > 0;
        }

        if (! is_array($attributes)) {
            $attributes = func_get_args();
        }

        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute, $dirty)) {
                return true;
            }
        }

        return false;
    }

    /**
     * [getDirty description]
     *
     * @return  [type]  [return description]
     */
    public function getDirty()
    {
        $dirty = array();

        foreach ($this->attributes as $key => $value) {
            if (! array_key_exists($key, $this->original)) {
                $dirty[$key] = $value;
            } else if (($value !== $this->original[$key]) && ! $this->originalIsEquivalent($key)) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * [originalIsEquivalent description]
     *
     * @param   [type]  $key  [$key description]
     *
     * @return  [type]        [return description]
     */
    protected function originalIsEquivalent($key)
    {
        $current = $this->attributes[$key];
        $original = $this->original[$key];

        if(is_numeric($current) && is_numeric($original)) {
            return (strcmp((string) $current, (string) $original) === 0);
        }

        return false;
    }

    /**
     * [load description]
     *
     * @param   [type]  $relations  [$relations description]
     *
     * @return  [type]              [return description]
     */
    public function load($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        foreach ($relations as $name) {
            if(array_key_exists($name, $this->relations) && method_exists($this, $name)) {
                $relation = call_user_func(array($this, $name));

                $this->relations[$name] = $relation->get();
            }
        }

        return $this;
    }

    /**
     * [with description]
     *
     * @param   [type]  $relations  [$relations description]
     *
     * @return  [type]              [return description]
     */
    public static function with($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }

        $instance = new static();

        return $instance->newBuilder()->with($relations);
    }

    /**
     * [newBuilder description]
     *
     * @return  [type]  [return description]
     */
    public function newBuilder()
    {
        return new Builder($this, $this->connection);
    }

    /**
     * [newPivot description]
     *
     * @param   Model  $parent      [$parent description]
     * @param   array  $attributes  [$attributes description]
     * @param   [type] $table       [$table description]
     * @param   [type] $exists      [$exists description]
     *
     * @return  [type]              [return description]
     */
    public function newPivot(Model $parent, array $attributes, $table, $exists)
    {
        return new Pivot($parent, $attributes, $table, $exists);
    }

    /**
     * [afterLoad description]
     *
     * @return  [type]  [return description]
     */
    protected function afterLoad()
    {
        return true;
    }

    /**
     * [beforeSave description]
     *
     * @return  [type]  [return description]
     */
    protected function beforeSave()
    {
        return true;
    }

    /**
     * [beforeDestroy description]
     *
     * @return  [type]  [return description]
     */
    protected function beforeDestroy()
    {
        return true;
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
        $builder = $this->newBuilder();

        return call_user_func_array(array($builder, $method), $parameters);
    }

    /**
     * [__callStatic description]
     *
     * @param   [type]  $method      [$method description]
     * @param   [type]  $parameters  [$parameters description]
     *
     * @return  [type]               [return description]
     */
    public static function __callStatic($method, $parameters)
    {
        $instance = new static();

        return call_user_func_array(array($instance, $method), $parameters);
    }

    /**
     * [__set description]
     *
     * @param   [type]  $key    [$key description]
     * @param   [type]  $value  [$value description]
     *
     * @return  [type]          [return description]
     */
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * [__get description]
     *
     * @param   [type]  $name  [$name description]
     *
     * @return  [type]         [return description]
     */
    public function __get($name)
    {
        // If the name is of one of attributes, return its value.
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        if($this->exists && array_key_exists($name, $this->relations) && method_exists($this, $name)) {
            $data =& $this->relations[$name];

            if(empty($data)) {
                // If the current Relation data is empty, fetch the associated information.
                $relation = call_user_func(array($this, $name));

                $data = $relation->get();
            }

            return $data;
        }
    }

    /**
     * [__isset description]
     *
     * @param   [type]  $name  [$name description]
     *
     * @return  [type]         [return description]
     */
    public function __isset($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * [__unset description]
     *
     * @param   [type]  $name  [$name description]
     *
     * @return  [type]         [return description]
     */
    public function __unset($name)
    {
        unset($this->attributes[$name]);
    }

    /**
     * [belongsTo description]
     *
     * @param   [type]  $className   [$className description]
     * @param   [type]  $foreignKey  [$foreignKey description]
     *
     * @return  [type]               [return description]
     */
    protected function belongsTo($className, $foreignKey = null)
    {
        return new BelongsTo($className, $this, $foreignKey);
    }

    /**
     * [hasOne description]
     *
     * @param   [type]  $className   [$className description]
     * @param   [type]  $foreignKey  [$foreignKey description]
     *
     * @return  [type]               [return description]
     */
    protected function hasOne($className, $foreignKey = null)
    {
        return new HasOne($className, $this, $foreignKey);
    }

    /**
     * [hasMany description]
     *
     * @param   [type]  $className   [$className description]
     * @param   [type]  $foreignKey  [$foreignKey description]
     *
     * @return  [type]               [return description]
     */
    protected function hasMany($className, $foreignKey = null)
    {
        return new HasMany($className, $this, $foreignKey);
    }

    /**
     * [belongsToMany description]
     *
     * @param   [type]  $className   [$className description]
     * @param   [type]  $joinTable   [$joinTable description]
     * @param   [type]  $foreignKey  [$foreignKey description]
     * @param   [type]  $otherKey    [$otherKey description]
     *
     * @return  [type]               [return description]
     */
    protected function belongsToMany($className, $joinTable = null, $foreignKey = null, $otherKey = null)
    {
        $joinTable = ($joinTable !== null) ? $joinTable : $this->joiningTable($className);

        return new BelongsToMany($className, $this, $joinTable, $foreignKey, $otherKey);
    }

    /**
     * [joiningTable description]
     *
     * @param   [type]  $className  [$className description]
     *
     * @return  [type]              [return description]
     */
    protected function joiningTable($className)
    {
        $parent = class_basename($this->className);
        $related = class_basename($className);

        // Prepare an models array.
        $models = array(
            Inflector::tableize($parent),
            Inflector::tableize($related)
        );

        // Sort the models.
        sort($models);

        return implode('_', $models);
    }

    /**
     * [save description]
     *
     * @return  [type]  [return description]
     */
    public function save()
    {
        if (! $this->beforeSave()) {
            return false;
        }

        // Get a new Builder instance.
        $builder = $this->newBuilder();

        // Prepare the Data.
        $data = $this->prepareData($builder);

        if (! $this->exists) {
            // We are into INSERT mode.
            $insertId = $builder->insert($data);

            if($insertId !== false) {
                // Mark the instance as existing and setup it primary key value.
                $this->exists = true;

                $this->setAttribute($this->primaryKey, $insertId);

                $result = true;
            }
        } else if($this->isDirty()) {
            // When the Model exists and it is dirty, we are into UPDATE mode.
            $result = $builder->updateBy($this->primaryKey, $this->getKey(), $data);

            $result = ($result !== false) ? true : $result;
        } else {
            // The Model exists and is unchanged.
            $result = true;
        }

        if($result) {
            // Sync the original attributes if is dirty.
            if ($this->isDirty()) {
                $this->syncOriginal();
            }

            return true;
        }

        return false;
    }

    /**
     * [destroy description]
     *
     * @return  [type]  [return description]
     */
    public function destroy()
    {
        if (! $this->exists || ! $this->beforeDestroy()) {
            return false;
        }

        // Get a new Builder instance.
        $builder = $this->newBuilder();

        $key = $this->primaryKey;

        $result = $builder->deleteBy($key, $this->getKey());

        if($result !== false) {
            $this->exists = false;

            // There is no valid primaryKey anymore.
            unset($this->attributes[$key]);

            return true;
        }

        return false;
    }

    /**
     * [prepareData description]
     *
     * @param   Builder  $builder  [$builder description]
     *
     * @return  [type]             [return description]
     */
    public function prepareData(Builder $builder)
    {
        $data = array();

        $fields = ! empty($this->fields) ? $this->fields : $builder->getFields();

        // Remove any protected attributes; the primaryKey is skipped by default.
        $skippedFields = array_merge((array) $this->primaryKey, $this->protectedFields);

        $fields = array_diff($fields, $skippedFields);

        // Walk over the defined Table Fields and prepare the data entries.
        foreach ($fields as $fieldName) {
            if(! array_key_exists($fieldName, $this->attributes)) {
                continue;
            }

            $value = $this->attributes[$fieldName];

            if(in_array($fieldName, (array) $this->serialize) && ! empty($value)) {
                $data[$fieldName] = serialize($value);
            } else {
                $data[$fieldName] = $value;
            }
        }

        // Process the timestamps.
        if ($this->timestamps) {
            $timestamps = array($this->createdField, $this->modifiedField);
        } else {
            $timestamps = array();
        }

        foreach($timestamps as $fieldName) {
            if(in_array($fieldName, $fields) && ! array_key_exists($fieldName, $data)) {
                $data[$fieldName] = $this->getDate();
            }
        }

        return $data;
    }

    /**
     * [getDate description]
     *
     * @param   [type]  $userDate  [$userDate description]
     *
     * @return  [type]             [return description]
     */
    protected function getDate($userDate = null)
    {
        $curr_date = ! empty($userDate) ? $userDate : time();

        switch ($this->dateFormat) {
            case 'int':
                return $curr_date;
                break;
            case 'datetime':
                return date('Y-m-d H:i:s', $curr_date);
                break;
            case 'date':
                return date('Y-m-d', $curr_date);
                break;
        }
    }

    /**
     * [getDebugInfo description]
     *
     * @return  [type]  [return description]
     */
    public function getDebugInfo()
    {
        // Prepare the Cache token.
        $token = $this->connection .'_' .$this->table;

        // Prepare the Table fields.
        if(! empty($this->fields))  {
            $fields = $this->fields;
        } else if(Builder::hasCached($token)) {
            // There was a Builder instance who use this table.
            $fields = Builder::getCache($token);
        } else {
            $builder = $this->newBuilder();

            $fields = $builder->getFields();
        }

        // There we store the parsed output.
        $result = '';

        // Support for checking if an object is empty
        $isEmpty = true;

        if (! $this->exists) {
            foreach ($fields as $fieldName) {
                if (isset($this->attributes[$fieldName])) {
                    $isEmpty = false;

                    break;
                }
            }
        }

        $result = $this->className .(! empty($this->getKey()) ? " #" . $this->getKey() : "") . "\n";

        $result .= "\tExists: " . ($this->exists ? "YES" : "NO") . "\n\n";

        if (! $this->exists && $isEmpty) {
            return $result;
        }

        foreach ($fields as $fieldName) {
            $result .= "\t" . Inflector::classify($fieldName) . ': ' .var_export($this->getAttribute($fieldName), true) . "\n";
        }

        if(! empty($this->relations)) {
            $result .= "\t\n";

            foreach ($this->relations as $name => $data) {
                $relation = call_user_func(array($this, $name));

                $result .= "\t" .ucfirst($relation->type())  .': ' .$name .' -> ' .$relation->getClass() . "\n";
            }
        }

        return $result;
    }

    /**
     * [getObjectVars description]
     *
     * @return  [type]  [return description]
     */
    public function getObjectVars()
    {
        return get_object_vars($this);
    }

}
