<?php

namespace Lorinczdev\Modely\Routing;

class Router
{
    public static function registerRoutes(string $integration, string $pathToRoutes): void
    {
        app(Route::class)
            ->use($integration)
            ->loadRoutes($pathToRoutes);

        app(Route::class)->compile();
    }
}
