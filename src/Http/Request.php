<?php

namespace Lorinczdev\Modely\Http;

use Illuminate\Support\Str;
use Lorinczdev\Modely\Models\Model;
use Lorinczdev\Modely\Models\Query;
use Lorinczdev\Modely\Routing\Route;
use Lorinczdev\Modely\Routing\UnknownRouteException;

class Request
{
    protected array $parameters = [];

    public function __construct(
        protected ?Model $model = null,
        protected ?Query $query = null
    )
    {
    }

    public static function for(
        ?Model $model = null,
        ?Query $query = null
    ): static
    {
        return new static(...func_get_args());
    }

    public function send(string $action, array $data = []): Response
    {
        $client = $this->getModel()->getClient();

        $route = app(Route::class)->find($this->getModel()::class, $action);

        if (!$route) {
            $modelClass = $this->getModel()::class;

            throw new UnknownRouteException(
                "Route for action [$action] on model [{$modelClass}] was not registered."
            );
        }

        $url = $this->prepareUrl($route);

        $response = $client->{$route['method']}($url, $data);

        return new Response($response);
    }

    protected function prepareUrl(array $route): string
    {
        $url = $route['url'];

        foreach ([...$this->getModel()->getAttributes(), ...$this->parameters] as $key => $value) {
            $url = Str::replace('{' . $key . '}', $value, $url);
        }

        if ($this->query) {
            $url .= $this->buildQuery();
        }

        return $url;
    }

    protected function buildQuery(): string
    {
        return $this->query->builder()->build();
    }

    protected function getModel(): Model
    {
        return $this->model ?? $this->query->getModel();
    }

    public function withParameters(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }
}
