<?php

namespace Lorinczdev\Modely\Tests;

use Lorinczdev\Modely\ModelyServiceProvider;
use Lorinczdev\Modely\Tests\Mocks\AnotherIntegration\AnotherIntegrationServiceProvider;
use Lorinczdev\Modely\Tests\Mocks\Integration\IntegrationServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelRay\RayServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array<int, string>
     */
    protected function getPackageProviders($app)
    {
        return [
            RayServiceProvider::class,
            ModelyServiceProvider::class,
            IntegrationServiceProvider::class,
            AnotherIntegrationServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
