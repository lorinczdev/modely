<?php

namespace Lorinczdev\Modely\Routing;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Lorinczdev\Modely\Modely;

class Route
{
    protected array $routes = [];

    protected array $compiledRoutes = [];

    protected string $integration;

    public function __construct()
    {
        //
    }

    public function use($integration): self
    {
        $this->integration = $integration;

        return $this;
    }

    public function getIntegration(): string
    {
        return $this->integration;
    }

    public function loadRoutes($path): void
    {
        require $path;
    }

    public static function get(string $url, array $action): void
    {
        self::new()->addRoute('GET', $url, $action);
    }

    public static function post(string $url, array $action): void
    {
        self::new()->addRoute('POST', $url, $action);
    }

    public static function put(string $url, array $action): void
    {
        self::new()->addRoute('PUT', $url, $action);
    }

    public static function patch(string $url, array $action): void
    {
        self::new()->addRoute('PATCH', $url, $action);
    }

    public static function delete(string $url, array $action): void
    {
        self::new()->addRoute('DELETE', $url, $action);
    }

    // public static function postFormData(string $url, array $action): void
    // {
    //     self::new()->addRoute('postFormData', $url, $action);
    // }

    public static function resource(string $url, string $model): RouteResource
    {
        $resource = new RouteResource($url, $model);

        $route = self::new();

        $route->pushToRouteCollection($resource);

        return $resource;
    }

    protected static function new(): self
    {
        return app(self::class);
    }

    protected function addRoute(string $method, string $url, array $action): void
    {
        [$model, $name] = $action;

        $route = [
            'url' => $url,
            'method' => $method,
            'model' => $model,
            'action' => $name,
        ];

        $this->pushToRouteCollection($route);
    }

    protected function pushToRouteCollection(array|RouteResource $route): void
    {
        if (!isset ($this->routes[$this->integration])) {
            $this->routes[$this->integration] = collect();
        }

        $this->routes[$this->integration]->push($route);
    }

    public function routesByIntegration(string $integration): Collection
    {
        return $this->routes[$integration] ?? collect();
    }

    public function compiledRoutesByIntegration(string $integration): Collection
    {
        return $this->compiledRoutes[$integration] ?? collect();
    }

    public function find(string $model, string $action): ?array
    {
        $config = Modely::getConfig($model);

        $routes = $this->compiledRoutes[$config['name']] ?? collect();

        return $routes->where('model', $model)->where('action', $action)->first();
    }

    public function compile(): void
    {
        // if (Cache::has('modely.routes')) {
        //     $this->compiledRoutes = Cache::get('modely.routes');
        //
        //     return;
        // }

        collect($this->routes)
            ->each(function (Collection $routes, string $integration) {
                $this->use($integration);

                $routes->each(function ($route) {
                    if ($route instanceof RouteResource) {
                        $route->compile();
                    }
                });
            });

        $this->compiledRoutes = collect($this->routes)
            ->map(function (Collection $routes, string $integration) {
                $this->use($integration);

                return $routes->filter(fn($route) => !($route instanceof RouteResource));
            })
            ->all();
    }

    public function cacheCompiledRoutes(): void
    {
        Cache::set('modely.routes', $this->compiledRoutes);
    }
}
