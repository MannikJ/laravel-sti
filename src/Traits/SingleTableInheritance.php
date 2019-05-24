<?php

namespace MannikJ\Laravel\SingleTableInheritance\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;


trait SingleTableInheritance
{
    protected $typeColumn;

    public function __construct(array $attributes = [])
    {
        $typeColumn = $this->getTypeColumn();
        if (!array_has($attributes, $typeColumn) && static::class != self::class) {
            $this->attributes[$typeColumn] = static::class;
        }
        parent::__construct($attributes);
    }

    protected static function boot()
    {
        if (is_subclass_of(get_called_class(), get_class())) {
            static::addGlobalScope('type', function (Builder $builder) {
                $prefixedTypeColumn = "{$builder->getModel()->getTable()}.{$builder->getModel()->getTypeColumn()}";
                return $builder->where($prefixedTypeColumn, static::class);
            });
        }
        static::saved(function ($model) {
            $model->handleSaved();
        });
        parent::boot();
    }

    public function handleSaved()
    { }

    public function getMorphClass()
    {
        return self::class;
    }

    public function getTypeColumn()
    {
        return isset($this->typeColumn) ? $this->typeColumn : config('single-table-inheritance.default_type_column', 'type');
    }

    public function getTypeAttribute()
    {
        $type = array_get($this->attributes, $this->getTypeColumn());
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
        $class = array_get((array)$attributes, 'type');

        $model = class_exists($class) ? new $class : $this;

        $model = $model->newInstance([], true);

        $model->setRawAttributes((array)$attributes, true);

        $model->setConnection($connection ?: $this->getConnectionName());

        $model->fireModelEvent('retrieved', false);

        return $model;
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

    /**
     * {@inheritDoc}
     */
    public function newInstance($attributes = [], $exists = false)
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.

        $attributes = (array)$attributes;

        $class = array_get($attributes, 'type', array_get($this->attributes, 'type', static::class));

        $model = new $class($attributes);

        $model->exists = $exists;

        $model->setConnection(
            $this->getConnectionName()
        );

        $model->setTable($this->getTable());

        return $model;
    }
}
