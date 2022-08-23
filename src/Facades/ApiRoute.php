<?php

namespace Lorinczdev\Modely\Facades;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lorinczdev\Modely\Routing\ApiRouter;
use Lorinczdev\Modely\Routing\ApiRouteResource;

/**
 * @method static ApiRouteResource resource(string $uri, string $model)
 * @method static \Lorinczdev\Modely\Routing\ApiRoute delete(string $uri, array|string $action)
 * @method static \Lorinczdev\Modely\Routing\ApiRoute get(string $uri, array|string $action)
 * @method static \Lorinczdev\Modely\Routing\ApiRoute patch(string $uri, array|string $action)
 * @method static \Lorinczdev\Modely\Routing\ApiRoute post(string $uri, array|string $action)
 * @method static \Lorinczdev\Modely\Routing\ApiRoute put(string $uri, array|string $action)
 * @method static self setIntegration(string $integration)
 * @method static Collection getRoutesByIntegration(string $integration)
 * @method static Collection getCompiledRoutesByIntegration(string $integration)
 * @method static Collection getRoutes()
 * @method static \Lorinczdev\Modely\Routing\ApiRoute prefix(string $prefix)
 * @method static void group(Closure|string|array $attributes, Closure|string $routes)
 * @method static void compile()
 * @method static string getIntegration()
 * @method static \Lorinczdev\Modely\Routing\ApiRoute find(string $model, string $action, string $method = null)
 *
 * @see ApiRouter
 */
class ApiRoute extends Facade
{
    /**
     * Resolve a new instance for the facade
     *
     * @return mixed
     */
    public static function refresh()
    {
        static::clearResolvedInstance(static::getFacadeAccessor());

        return static::getFacadeRoot();
    }

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return ApiRouter::class;
    }
}
