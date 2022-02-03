# A single table interitance trait for Eloquent

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mannikj/laravel-sti.svg?style=flat-square)](https://packagist.org/packages/mannikj/laravel-sti)
[![Build Status](https://img.shields.io/travis/mannikj/laravel-sti/master.svg?style=flat-square)](https://travis-ci.org/mannikj/laravel-sti)
[![Quality Score](https://img.shields.io/scrutinizer/g/mannikj/laravel-sti.svg?style=flat-square)](https://scrutinizer-ci.com/g/mannikj/laravel-sti)
[![Total Downloads](https://img.shields.io/packagist/dt/mannikj/laravel-sti.svg?style=flat-square)](https://packagist.org/packages/mannikj/laravel-sti)

This package provides a trait that makes your eloquent models capable of single table inheritance.
If configured properly, queries will automatically return instances of the correct model subclasses according to the type column.

## Installation

You can install the package via composer:

```bash
composer require mannikj/laravel-sti
```

## Usage

### Migration

The table you want to apply single table inheritance to must incorporate a type column.

The `STI` facade provides a helper to create the type column.

```php
Schema::table('table', function (Blueprint $table) {
    \STI::column($table)->nullable();
});
```

### Using the Trait

You need to add the `SingleTableInheritance` trait to your root model class.
The sub models need to extend the root class.

```php
use MannikJ\Laravel\SingleTableInheritance\Traits\SingleTableInheritance;

class Root {
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

class Root {
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
