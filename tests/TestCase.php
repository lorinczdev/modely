<?php

namespace Lorinczdev\Modely\Tests;

use Illuminate\Foundation\Application;
use Lorinczdev\Modely\ModelyServiceProvider;
use Lorinczdev\Modely\Tests\Mocks\Integration\IntegrationServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            ModelyServiceProvider::class,
            IntegrationServiceProvider::class,
        ];
    }
}
