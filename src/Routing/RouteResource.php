<?php

namespace Lorinczdev\Modely\Routing;

use Illuminate\Support\Arr;
use RuntimeException;

class RouteResource
{
    protected array $actions = [
        'index', 'show', 'store', 'update', 'destroy',
    ];

    protected array $actionGroups = [
        'crud' => ['index', 'show', 'store', 'update', 'destroy'],
    ];

    protected array $actionOptions = [
        'index' => [
            'method' => 'get',
            'url' => '',
        ],
        'show' => [
            'method' => 'get',
            'url' => '/{id}',
        ],
        'store' => [
            'method' => 'put',
            'url' => '',
        ],
        'update' => [
            'method' => 'post',
            'url' => '/{id}',
        ],
        'destroy' => [
            'method' => 'delete',
            'url' => '/{id}',
        ],
    ];

    public function __construct(
        protected string $url,
        protected string $model
    )
    {
        //
    }

    public function only(array|string $actions = []): static
    {
        if (func_num_args() > 1) {
            $actions = func_get_args();
        }

        $actions = Arr::wrap($actions);

        $this->actions = $actions;

        return $this;
    }

    public function except(array|string $excludedMethods = []): static
    {
        if (func_num_args() > 1) {
            $excludedMethods = func_get_args();
        }

        $excludedMethods = Arr::wrap($excludedMethods);

        $this->actions = array_values(array_diff($this->actions, $excludedMethods));

        return $this;
    }

    public function additionalActions(array|string $actions = []): self
    {
        if (func_num_args() > 1) {
            $actions = func_get_args();
        }

        $actions = Arr::wrap($actions);

        $serviceActions = app(RouteResourceOptions::class)->getActions();
        $serviceGroups = app(RouteResourceOptions::class)->getGroups();

        foreach ($actions as $action) {
            if (Arr::has($serviceGroups, $action)) {
                $this->actions = [
                    ...$this->actions,
                    ...$serviceGroups[$action]
                ];

                continue;
            }

            if (!Arr::has($serviceActions, $action)) {
                throw new RuntimeException("Invalid action: {$action}");
            }

            $this->actions[] = $action;
        }

        return $this;
    }

    public function compile(): void
    {
        $serviceActions = app(RouteResourceOptions::class)->getActions();

        $actionOptions = [
            ...$this->actionOptions,
            ...$serviceActions
        ];

        foreach ($this->actions as $actionName) {
            $action = $actionOptions[$actionName];

            Route::{$action['method']}($this->url . $action['url'], [$this->model, $actionName]);
        }
    }
}
