<?php

namespace Lorinczdev\Modely\Tests\Mocks\AnotherIntegration;

use Illuminate\Support\ServiceProvider;
use Lorinczdev\Modely\Modely;

class AnotherIntegrationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
    }

    public function register(): void
    {
        app(Modely::class)
            ->register('another-integration', __DIR__);
    }
}
