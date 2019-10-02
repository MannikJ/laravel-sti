<?php

namespace MannikJ\Laravel\SingleTableInheritance\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;


trait SingleTableInheritance
{

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->ensureTypeCharacteristics();
    }

    public static function bootSingleTableInheritance()
    {
        if (static::isSubClass()) {
            static::addGlobalScope('type', function (Builder $builder) {
                $builder->sti();
            });
        }
    }

    public function ensureTypeCharacteristics($attributes = [])
    {
        if ($this->resolveTypeViaAttributes($attributes)) {
            return;
        }

        if ($type = $this->resolveTypeViaClass()) {
            $this->applyTypeCharacteristics($type);
        }
    }

    public static function getStiSubClasses()
    {
        $map = [];
        foreach (static::getDirectStiSubClasses() as $subClass) {
            $map += $subClass::getStiSubClasses();
        }
        return $map;
    }

    public static function getStiSubTypes()
    {
        return array_keys(static::getStiMap());
    }

    public static function getStiMap(): array
    {
        return collect(static::getStiSubClasses())
            ->mapWithKeys(function ($subClass) {
                return [$subClass::resolveTypeViaClass() => $subClass];
            })->toArray();
    }

    public static function getDirectStiSubClasses(): array
    {
        try {
            if (
                !static::$stiSubClasses
                && parent::$stiSubClasses === static::$stiSubClasses
            ) {
                return [];
            }
            return static::$stiSubClasses;
        } catch (\Throwable $th) {
            return [];
        }
    }

    public static function getTypesForScope()
    {
        return [static::resolveTypeViaClass()] + static::getStiSubTypes();
    }

    public function resolveTypeViaAttributes($attributes = null)
    {
        $attributes = $attributes ?: $this->attributes;
        return ($attribute = $this->getTypeColumn())
            ? array_get($attributes, $attribute)
            : null;
    }

    public static function resolveTypeViaClass()
    {
        return static::isSubClass() ? static::class : null;
    }

    /**
     * @param string $type
     */
    public function applyTypeCharacteristics($type)
    {
        $this->attributes[$this->getTypeColumn()] = $type;
    }

    public static function isSubClass()
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
        $attributes = (array) $attributes;

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

        $attributes = (array) $attributes;

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
