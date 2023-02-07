# A single table inheritance trait for Eloquent

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mannikj/laravel-sti.svg?style=flat-square)](https://packagist.org/packages/mannikj/laravel-sti)
[![Build Status](https://img.shields.io/travis/mannikj/laravel-sti/master.svg?style=flat-square)](https://travis-ci.org/mannikj/laravel-sti)
[![Total Downloads](https://img.shields.io/packagist/dt/mannikj/laravel-sti.svg?style=flat-square)](https://packagist.org/packages/mannikj/laravel-sti)

This package provides a trait that makes your eloquent models capable of single table inheritance.
If configured properly, queries will automatically return instances of the correct model subclasses according to the type column.

## Installation

You can install the package via composer:

```bash
composer require mannikj/laravel-sti
```

## Basic Usage

### Migration

The table you want to apply single table inheritance to must incorporate a type column.

The `STI` facade provides a helper to create the type column.

```php
Schema::table('table', function (Blueprint $table) {
    $table->sti()->nullable();
});
```

### Using the Trait

You need to add the `SingleTableInheritance` trait to your root model class.
The sub models need to extend the root class.

```php
use MannikJ\Laravel\SingleTableInheritance\Traits\SingleTableInheritance;

class Root extends Model {
    use SingleTableInheritance;
}

class Sub1 extends Root {}

class Sub2 extends Root {}
```

For the default usage no other configuration is needed.
The trait will use the class name of the subclasses as the type, scope the queries accordingly and return the correct instances
of subclasses automatically.

#### Nested

If you have multiple levels of subclasses and you want the automatic scoping to include all sub types, you need to define the direct subclasses for each model by setting the `stiSubClasses` array property:

```php
use MannikJ\Laravel\SingleTableInheritance\Traits\SingleTableInheritance;

class Root extends Model {
    use SingleTableInheritance;

    protected $stiSubClasses = [
        Sub1::class, Sub2::class
    ]
}

class Sub1 extends Root {
    protected $stiSubClasses = [
        Sub3::class
    ]
}

class Sub2 extends Root {}

class Sub3 extends Sub1 {}
```

### Advanced Usage

#### Types without fully-qualified classnames

Without any tweaking, the trait assumes that there is a type column storing the fully qualified names of the subclasses. 
However, if you want to use some other string not directly referencing a class as the type identifier, you can do so by overwriting two functions of the trait:

```php
    public static function resolveTypeViaClass()
    {
        $type = (new \ReflectionClass(static::class))->getShortName();
        $type = str_replace('Component', '', $type);
        $type = strtolower($type);

        return static::isSubclass() 
            ? $type 
            : null;
    }


    public function resolveModelClassViaAttributes($attributes = [])
    {
        $type = $this->resolveTypeViaAttributes($attributes);

        // Map class to type
        $mapping = [
            'motif' => MotifComponent::class,
            'text' => TextComponent::class,
        ];
        
        return $type 
            ? data_get($mapping, $type) 
            : static::class;
    }
```
#### Resolving the type based on a related model

You can also tweak the behavior even further so that the type will be determined based on a related model:

```php
class Animal extends Model
{
    use SingleTableInheritance;

    protected $fillable = ['name'];

    public function resolveTypeViaAttributes($attributes = [])
    {
        if ($category = Category::find(Arr::get($attributes, 'category_id'))) {
            return $category->config_class;
        };
    }

    public function applyTypeCharacteristics($type)
    {
        $this->category_id = Category::where('config_class', $type)->first()?->id;
    }

    public function scopeSti(Builder $builder)
    {
        $builder->whereHas('category', function ($query) use ($builder) {
            $query->where('categories.config_class', static::class);
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id')->withDefault([
            'config_class' => static::class
        ]);
    }
}

```


### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information about what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email mannikj@web.de instead of using the issue tracker.

## Credits

-   [Jannik Malken](https://github.com/mannikj)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
