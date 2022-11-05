<?php

namespace Lorinczdev\Modely\Routing;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ApiRouteResource
{
    protected array $options = [];

    protected array $actions = [
        'index', 'show', 'store', 'update', 'destroy',
    ];

    protected array $actionGroups = [];

    protected array $actionSets = [
        'index' => [
            'method' => 'GET',
            'uri' => '',
        ],
        'show' => [
            'method' => 'GET',
            'uri' => '/{model}',
        ],
        'store' => [
            'method' => 'PUT',
            'uri' => '',
        ],
        'update' => [
            'method' => 'POST',
            'uri' => '/{model}',
        ],
        'destroy' => [
            'method' => 'DELETE',
            'uri' => '/{model}',
        ],
    ];

    public function __construct(
        protected string $uri,
        protected string $model
    ) {
        //
    }

    /**
     * Set the methods the controller should apply to.
     */
    public function only(array|string $methods): self
    {
        $this->options['only'] = is_array($methods) ? $methods : func_get_args();

        return $this;
    }

    /**
     * Set the methods the controller should exclude.
     */
    public function except(array|string $methods): self
    {
        $this->options['except'] = is_array($methods) ? $methods : func_get_args();

        return $this;
    }

    public function addActionGroup(array|string $groups): self
    {
        $groups = is_array($groups) ? $groups : func_get_args();

        $this->actionGroups = array_merge($this->actionGroups, $groups);

        return $this;
    }

    public function compile(Collection $routes): void
    {
        $actionSets = $this->getActionSets($routes->where('reusableAction', true));

        foreach ($this->getActions() as $actionName) {
            $action = $actionSets[$actionName];

            $method = strtolower($action['method']);

            \Lorinczdev\Modely\Facades\ApiRoute::{$method}($this->resolveUri($this->uri.$action['uri']), [$this->model, $actionName]);
        }
    }

    protected function getActionSets(Collection $routes): array
    {
        $this->loadGroups(
            $routes->groupBy('partOfGroup')
        );

        foreach ($this->actions as $action) {
            $route = $routes->firstWhere('action', $action);

            if (! $route) {
                continue;
            }

            $this->actionSets[$action] = [
                'method' => $route->method,
                'uri' => $route->uri,
            ];
        }

        return $this->actionSets;
    }

    protected function resolveUri(string $uri): string
    {
        return Str::replace('{model}', '{'.(new $this->model)->getKeyName().'}', $uri);
    }

    public function loadGroups(Collection $routes): void
    {
        foreach ($this->actionGroups as $group) {
            $collection = $routes[$group] ?? [];

            if (! $collection) {
                continue;
            }

            $this->addAction($collection->pluck('action')->all());
        }
    }

    public function addAction(array|string $actions): self
    {
        $actions = is_array($actions) ? $actions : func_get_args();

        $this->actions = array_merge($this->actions, $actions);

        return $this;
    }

    public function getActions(): array
    {
        $actions = $this->actions;

        if (isset($this->options['only'])) {
            $actions = array_intersect($actions, (array) $this->options['only']);
        }

        if (isset($this->options['except'])) {
            $actions = array_diff($actions, (array) $this->options['except']);
        }

        return $actions;
    }
}
