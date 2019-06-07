<?php

namespace MannikJ\Laravel\SingleTableInheritance\Tests;

use Orchestra\Testbench\TestCase;
use MannikJ\Laravel\SingleTableInheritance\SingleTableInheritanceServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

abstract class LaravelTest extends TestCase
{
    use RefreshDatabase;
    use ArraySubsetAsserts;
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->withFactories(__DIR__ . '/database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            SingleTableInheritanceServiceProvider::class
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'STI' => 'MannikJ\Laravel\SingleTableInheritance\Facades\STI'
        ];
    }
}
