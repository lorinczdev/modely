<?php

namespace Lorinczdev\Modely\Routing;

use Illuminate\Support\Arr;

class RouteResourceOptions
{
    protected array $groups = [];

    protected array $actions = [];

    public function __construct()
    {
        $serviceName = app(Route::class)->getIntegration();

        if (!Arr::has($this->groups, $serviceName)) {
            $this->groups[$serviceName] = [];
        }

        if (!Arr::has($this->actions, $serviceName)) {
            $this->actions[$serviceName] = [];
        }
    }

    public function getGroups(): array
    {
        $serviceName = app(Route::class)->getIntegration();

        return $this->groups[$serviceName] ?? [];
    }

    public static function addGroup(string $name, array $routes): void
    {
        $routeResourceOptions = app(self::class);

        $routeNames = array_keys(Arr::collapse($routes));

        $routeResourceOptions->pushGroup($name, $routeNames);
    }

    protected function pushGroup(string $name, array $routeNames): void
    {
        $serviceName = app(Route::class)->getIntegration();

        $this->groups[$serviceName][$name] = $routeNames;
    }

    public function getActions(): array
    {
        $serviceName = app(Route::class)->getIntegration();

        return $this->actions[$serviceName] ?? [];
    }

    public static function addAction(string $name, array $data): array
    {
        $routeResourceOptions = app(self::class);

        $routeResourceOptions->pushAction($name, $data);

        return [$name => $data];
    }

    protected function pushAction(string $name, array $data): void
    {
        $serviceName = app(Route::class)->getIntegration();

        $this->actions[$serviceName][$name] = $data;
    }
}
