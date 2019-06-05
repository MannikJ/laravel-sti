<?php

namespace MannikJ\Laravel\SingleTableInheritance\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait SingleTableInheritance
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->checkType($attributes);
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

    public function applyTypeCharacteristics($type)
    {
        $this->attributes[$this->getTypeColumn()] = $type;
    }

    protected static function boot()
    {
        if (static::isSubclass()) {
            static::addGlobalScope('type', function (Builder $builder) {
                static::typeScope($builder);
            });
        }
        parent::boot();
        static::saved(function ($model) {
            $model->handleSaved($model);
        });
    }

    public static function isSubclass()
    {
        return is_subclass_of(get_called_class(), static::getBaseModelClass());
    }

    public static function getBaseModelClass()
    {
        return self::class;
    }

    public static function typeScope(Builder $builder)
    {
        $prefixedTypeColumn = "{$builder->getModel()->getTable()}.{$builder->getModel()->getTypeColumn()}";
        return $builder->where($prefixedTypeColumn, static::class);
    }

    public function handleSaved()
    { }

    public function getMorphClass()
    {
        return self::class;
    }

    public function getTypeColumn()
    {
        return isset($this->typeColumn)
            ? $this->typeColumn
            : config('single-table-inheritance.default_type_column', 'type');
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

    public function getSubModelClass($attributes = [])
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

        $class = $this->getSubModelClass($attributes);

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

        $class = $this->getSubModelClass($attributes) ?: static::class;

        $model = new $class($attributes);

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        $model->setTable($this->getTable());

        return $model;
    }
}
