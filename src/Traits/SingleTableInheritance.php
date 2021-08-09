<?php

namespace MannikJ\Laravel\SingleTableInheritance\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait SingleTableInheritance
{
    public static function bootSingleTableInheritance()
    {
        if (static::isSubClass()) {
            static::addGlobalScope('sti', function (Builder $builder) {
                return $builder->sti();
            });
        }
    }

    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if ($this->resolveTypeViaAttributes($attributes)) {
            return;
        }

        if ($type = $this->resolveTypeViaClass()) {
            $this->applyTypeCharacteristics($type);
        }
    }

    public static function getStiMap(): array
    {
        return collect(static::getStiSubClasses())
            ->mapWithKeys(function ($subClass) {
                return [$subClass::resolveTypeViaClass() => $subClass];
            })->toArray();
    }

    public static function getStiSubTypes(): array
    {
        return array_keys(static::getStiMap());
    }

    /**
     * Recursively traverse all sti sub classes
     */
    public static function getStiSubClasses(): array
    {
        $classes = collect(static::getDirectStiSubClasses());
        foreach (static::getDirectStiSubClasses() as $subClass) {
            $classes = $classes->merge($subClass::getStiSubClasses());
        }
        return $classes->unique()->toArray();
    }

    public static function getDirectStiSubClasses(): array
    {
        if (!property_exists(static::class, 'stiSubClasses')) {
            return [];
        }

        $parent = get_parent_class(static::class);

        if (
            isset($parent::$stiSubClasses)
            && $parent::$stiSubClasses === static::$stiSubClasses
        ) {
            return [];
        }

        return static::$stiSubClasses;
    }

    public static function getTypesForScope(): array
    {
        return array_merge([static::resolveTypeViaClass()], static::getStiSubTypes());
    }

    public function resolveTypeViaAttributes($attributes = null): ?string
    {
        $attributes = $attributes ?: $this->attributes;
        return ($attribute = $this->getTypeColumn())
            ? Arr::get($attributes, $attribute)
            : null;
    }

    public static function resolveTypeViaClass(): ?string
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

    public static function isSubClass(): bool
    {
        return is_subclass_of(static::class, static::getBaseModelClass());
    }

    public static function getBaseModelClass(): string
    {
        return self::class;
    }

    public function scopeSti(Builder $builder, array $types = null)
    {
        $types = $types ?: static::getTypesForScope();

        return $builder
            ->whereIn(
                $builder->getModel()
                    ->getTypeColumn(
                        true
                    ),
                $types
            );
    }

    public function handleSaved()
    {
    }

    public function getMorphClass(): string
    {
        return self::class;
    }

    public function getTypeColumn($qualified = false): string
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
    public function getForeignKey(): string
    {
        return Str::snake(class_basename(self::class)) . '_' . $this->getKeyName();
    }

    public function getModelClassViaAttributes($attributes = []): ?string
    {
        return $this->resolveTypeViaAttributes($attributes);
    }

    public function getTypeAttribute(): ?string
    {
        $type = $this->resolveTypeViaAttributes($this->attributes);
        $type = Str::kebab(class_basename($type));
        return $type ?: null;
    }

    public function getTable(): string
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
