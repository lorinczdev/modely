<?php

namespace Lorinczdev\Modely\Routing;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Lorinczdev\Modely\Modely;

class ApiRouter
{
    protected array $routes;

    protected array $compiledRoutes = [];

    protected string $integration;

    /**
     * The route group attribute stack.
     */
    protected array $groupStack = [];

    public function __construct()
    {
        $this->compiledRoutes = $this->getCachedRoutes();
    }

    public function loadRoutes(Closure|string $routes): void
    {
        if ($routes instanceof Closure) {
            $routes($this);
        } else {
            require $routes;
        }
    }

    public function compile(): void
    {
        $routes = $this->getRoutesByIntegration($this->integration);

        $routes
            ->each(function ($route) use ($routes) {
                if ($route instanceof ApiRouteResource) {
                    $route->compile($routes);
                }
            });

        $routes = $this->getRoutesByIntegration($this->integration);

        $this->compiledRoutes[$this->integration] = $routes
            ->filter(fn ($route) => ! ($route instanceof ApiRouteResource)
                && ! $route->reusableAction
            );
    }

    public function getRoutesByIntegration(string $integration): Collection
    {
        return $this->routes[$integration] ?? collect();
    }

    public function get(string $uri, array|string $action): ApiRoute
    {
        return $this->addRoute('GET', $uri, $action);
    }

    /**
     * Add a route to the underlying route collection.
     */
    public function addRoute(string $method, string $uri, string|array $action): ApiRoute
    {
        $this->pushToRouteCollection(
            $route = $this->createRoute($method, $uri, $action)
        );

        return $route;
    }

    protected function pushToRouteCollection(ApiRoute|ApiRouteResource $route): void
    {
        if (! isset ($this->routes[$this->integration])) {
            $this->routes[$this->integration] = collect();
        }

        $this->routes[$this->integration]->push($route);
    }

    /**
     * Create a new route instance.
     */
    protected function createRoute(string $method, string $uri, string|array $action): ApiRoute
    {
        $route = $this->newRoute(
            $method, $this->prefix($uri), $action
        );

        // If we have groups that need to be merged, we will merge them now after this
        // route has already been created and is ready to go. After we're done with
        // the merge we will be ready to return the route back out to the caller.
        // if ($this->hasGroupStack()) {
        //     $this->mergeGroupAttributesIntoRoute($route);
        // }

        return $route;
    }

    /**
     * Create a new Route object.
     */
    protected function newRoute(string $method, string $uri, string|array $action): ApiRoute
    {
        if (is_array($action)) {
            [$model, $action] = $action;
        } else {
            $model = null;
        }

        return (new ApiRoute($method, $uri, $action, $model))->setRouter($this);
    }

    /**
     * Prefix the given URI with the last prefix.
     */
    protected function prefix(string $uri): string
    {
        return trim(trim($this->getLastGroupPrefix(), '/') . '/' . trim($uri, '/'), '/') ?: '/';
    }

    /**
     * Get the prefix from the last group on the stack.
     */
    public function getLastGroupPrefix(): string
    {
        if ($this->hasGroupStack()) {
            $last = end($this->groupStack);

            return $last['prefix'] ?? '';
        }

        return '';
    }

    /**
     * Determine if the router currently has a group stack.
     */
    public function hasGroupStack(): bool
    {
        return ! empty($this->groupStack);
    }

    public function post(string $uri, array|string $action): ApiRoute
    {
        return $this->addRoute('POST', $uri, $action);
    }

    public function put(string $uri, array|string $action): ApiRoute
    {
        return $this->addRoute('PUT', $uri, $action);
    }

    public function patch(string $uri, array|string $action): ApiRoute
    {
        return $this->addRoute('PATCH', $uri, $action);
    }

    public function delete(string $uri, array|string $action): ApiRoute
    {
        return $this->addRoute('DELETE', $uri, $action);
    }

    public function resource(string $uri, string $model): ApiRouteResource
    {
        $resource = new ApiRouteResource($uri, $model);

        $this->pushToRouteCollection($resource);

        return $resource;
    }

    /**
     * Get the current group stack for the router.
     */
    public function getGroupStack(): array
    {
        return $this->groupStack;
    }

    /**
     * Create a route group with shared attributes.
     */
    public function group(array $attributes, array|string|Closure $routes): void
    {
        foreach (Arr::wrap($routes) as $groupRoutes) {
            $this->updateGroupStack($attributes);

            // Once we have updated the group stack, we'll load the provided routes and
            // merge in the group's attributes when the routes are created. After we
            // have created the routes, we will pop the attributes off the stack.
            $this->loadRoutes($groupRoutes);

            array_pop($this->groupStack);
        }
    }

    /**
     * Update the group stack with the given attributes.
     */
    protected function updateGroupStack(array $attributes): void
    {
        if ($this->hasGroupStack()) {
            $attributes = $this->mergeWithLastGroup($attributes);
        }

        $this->groupStack[] = $attributes;
    }

    /**
     * Merge the given array with the last group stack.
     */
    public function mergeWithLastGroup(array $new, bool $prependExistingPrefix = true): array
    {
        return ApiRouteGroup::merge($new, end($this->groupStack), $prependExistingPrefix);
    }

    public function getIntegration(): string
    {
        return $this->integration;
    }

    public function setIntegration(string $integration): self
    {
        $this->integration = $integration;

        return $this;
    }

    public function find(string $model, string $action, string $method = null): ?ApiRoute
    {
        $config = $model::$config;

        $routes = $this->compiledRoutes[$config['name']] ?? collect();

        $routes = $routes->where('model', $model)->where('action', $action);

        if ($method) {
            $routes = $routes->where('method', strtoupper($method));
        }

        return $routes->first();
    }

    public function getRoutes(): Collection
    {
        return collect($this->routes);
    }

    public function getCompiledRoutesByIntegration(string $integration): Collection
    {
        return $this->compiledRoutes[$integration] ?? collect();
    }

    public function getCachedRoutes(): array
    {
        return cache('modely.routes', []);
    }

    public function compileRoutes(): void
    {
        $integrations = app(Modely::class)->getIntegrations();

        foreach ($integrations as $name => $integration) {
            app(__CLASS__)
                ->setIntegration($name)
                ->loadRoutes($integration['routes']);
        }

        app(__CLASS__)->compile();
    }

    public function cacheRoutes(): void
    {
        cache(['modely.routes' => $this->compiledRoutes]);
    }
}
