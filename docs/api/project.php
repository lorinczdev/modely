<?php

use Illuminate\Support\ServiceProvider;
use Lorinczdev\Modely\Modely;

// Use service provider to register integration's config.
class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        app(Modely::class)
            ->register('integration', __DIR__);
    }
}
