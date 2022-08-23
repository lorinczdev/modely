<?php

namespace Lorinczdev\Modely\Tests\Mocks\Integration;

use Illuminate\Support\ServiceProvider;
use Lorinczdev\Modely\Modely;

class IntegrationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
    }

    public function register(): void
    {
        app(Modely::class)
            ->register('integration', __DIR__);
    }
}
