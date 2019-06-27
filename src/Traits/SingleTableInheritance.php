<?php

namespace MannikJ\Laravel\SingleTableInheritance\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;


trait SingleTableInheritance
{
    protected static $stiTypeMap = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->checkType($attributes);
    }

    public static function bootSingleTableInheritance()
    {
        static::getStiTypeMap();
        if (static::isSubclass()) {
            static::addGlobalScope('type', function (Builder $builder) {
                $builder->sti();
            });
        }
        static::saved(function ($model) {
            $model->handleSaved($model);
        });
    }

    public function checkType($attributes = [])
    {
        if ($this->resolveTypeViaAttributes($attributes)) {
            return;
        }

        if ($type = $this->resolveTypeViaClass()) {
            $this->applyTypeCharacteristics($type);
        }
    }

    public static function getStiTypeMap(): array
    {
        if (array_key_exists(static::class, self::$stiTypeMap)) {
            return self::$stiTypeMap[static::class];
        }
        $typeMap = [];
        // Check if the calledClass is a leaf of the hierarchy. stiSubclasses will be inherited from the parent class
        // so its important we check for the tableType first otherwise we'd infinitely recurse.
        if ($type = static::resolveTypeViaClass()) {
            $typeMap[$type] = static::class;
        }
        $subclasses = static::getStiSubclasses();
        // prevent infinite recursion if the singleTableSubclass is inherited
        if (!in_array(static::class, $subclasses)) {
            foreach ($subclasses as $subclass) {
                $typeMap = $typeMap + $subclass::getStiTypeMap();
            }
        }

        self::$stiTypeMap[static::class] = $typeMap;
        return $typeMap;
    }

    public static function getStiSubclasses(): array
    {
        return property_exists(static::class, 'stiSubclasses')
            ? static::$stiSubclasses
            : [];
    }

    public static function getAllStiSubclasses()
    {
        return array_keys(static::getStiTypeMap());
    }

    public function resolveTypeViaAttributes($attributes = [])
    {
        return ($attribute = $this->getTypeColumn())
            ? array_get($attributes, $attribute, array_get($this->attributes, $attribute))
            : null;
    }

    public static function resolveTypeViaClass()
    {
        return static::isSubclass() ? static::class : null;
    }

    public static function getTypesForScope()
    {
        return [static::class] + static::getAllStiSubclasses();
    }

    public function applyTypeCharacteristics($type)
    {
        $this->attributes[$this->getTypeColumn()] = $type;
    }

    public static function isSubclass()
    {
        return is_subclass_of(static::class, static::getBaseModelClass());
    }

    public static function getBaseModelClass()
    {
        return self::class;
    }

    public function scopeSti(Builder $builder)
    {
        return $builder->whereIn($builder->getModel()->getTypeColumn(true), static::getTypesForScope());
    }

    public function handleSaved()
    { }

    public function getMorphClass()
    {
        return self::class;
    }

    public function getTypeColumn($qualified = false)
    {
        $typeColumn = isset($this->typeColumn)
            ? $this->typeColumn
            : config('single-table-inheritance.default_type_column', 'type');

        return $qualified
            ? "{$this->getTable()}.{$typeColumn}"
            : $typeColumn;
    }

    /**
     * Get the default foreign key name for the model.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return Str::snake(class_basename(self::class)) . '_' . $this->getKeyName();
    }

    public function getModelClassViaAttributes($attributes = [])
    {
        return $this->resolveTypeViaAttributes($attributes);
    }

    public function getTypeAttribute()
    {
        $type = $this->resolveTypeViaAttributes($this->attributes);
        $type = Str::kebab(class_basename($type));
        return $type ?: null;
    }

    public function getTable()
    {
        if (!isset($this->table)) {
            return str_replace(
                '\\',
                '',
                Str::snake(Str::plural(class_basename(self::class)))
            );
        }
        return $this->table;
    }

    /**
     * {@inheritDoc}
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $attributes = (array)$attributes;

        $class = $this->getModelClassViaAttributes($attributes);

        $model = class_exists($class) ? new $class : $this;

        $model = $model->newInstance([], true);

        $model->setRawAttributes($attributes, true);

        $model->setConnection($connection ?: $this->getConnectionName());

        $model->fireModelEvent('retrieved', false);

        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function newInstance($attributes = [], $exists = false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.

        $attributes = (array)$attributes;

        $class = $this->getModelClassViaAttributes($attributes) ?: static::class;

        $model = new $class($attributes);

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        $model->setTable($this->getTable());

        return $model;
    }
}
