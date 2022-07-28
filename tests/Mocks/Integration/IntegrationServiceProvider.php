<?php

namespace Lorinczdev\Modely\Tests\Mocks\Integration;

use Carbon\Laravel\ServiceProvider;
use Lorinczdev\Modely\Modely;

class IntegrationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {

    }

    public function register(): void
    {
        app(Modely::class)->extend('integration', require __DIR__ . '/config.php');
    }
}
