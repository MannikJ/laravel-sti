# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mannikj/laravel-sti.svg?style=flat-square)](https://packagist.org/packages/mannikj/laravel-sti)
[![Build Status](https://img.shields.io/travis/mannikj/laravel-sti/master.svg?style=flat-square)](https://travis-ci.org/mannikj/laravel-sti)
[![Quality Score](https://img.shields.io/scrutinizer/g/mannikj/laravel-sti.svg?style=flat-square)](https://scrutinizer-ci.com/g/mannikj/laravel-sti)
[![Total Downloads](https://img.shields.io/packagist/dt/mannikj/laravel-sti.svg?style=flat-square)](https://packagist.org/packages/mannikj/laravel-sti)

This package provices a trait you can use to make your eloquent models capable of single table inheritance. ###If configured properly, queries will automatically return the instances of the correct model subclasses based on their type column.

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

### Trait

You need to add the `SingleTableInheritance` trait to your root model class.
The sub models need to extend the root class.

``` php
use MannikJ\Laravel\SingleTableInheritance\Traits\SingleTableInheritance;

class Root {
    use SingleTableInheritance;
}

class Sub1 extends Root {}

class Sub2 extends Root {}
```
For the default configuration no other configuration is needed.
The trait will the class name of the subclasses as the type value
and will scope the queries automatically and return the correct instance
of subclasses.

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email mannikj@web.de instead of using the issue tracker.

## Credits

- [Jannik Malken](https://github.com/mannikj)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).