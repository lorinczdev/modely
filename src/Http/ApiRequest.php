<?php

namespace Lorinczdev\Modely\Http;

use Illuminate\Support\Str;
use Lorinczdev\Modely\Models\Query;
use Lorinczdev\Modely\Routing\ApiRoute;
use Lorinczdev\Modely\Routing\ApiRouter;
use Lorinczdev\Modely\Routing\UnknownRouteException;

class ApiRequest
{
    protected array $parameters = [];

    public function __construct(protected Query $query)
    {
    }

    public static function use(Query $query): static
    {
        return new static(...func_get_args());
    }

    public function send(
        string $action,
        array $data = [],
        string $method = null
    ): ApiResponse {
        $client = $this->getClient();

        $route = $this->getRoute($action, $method);

        $url = $this->prepareUrl($route);

        match ($route->contentType) {
            'form' => $client->asForm(),
            'multipart' => $client->asMultipart(),
            default => '',
        };

        $response = $client->{$route->method}($url, $data);

        return $this->resolveResponse($response);
    }

    public function resolveResponse($response): ApiResponse
    {
        $responseClass = $this->query->getModel()->getConfig()['response'] ?? ApiResponse::class;

        return new $responseClass($response);
    }

    protected function getClient(): ApiClient
    {
        return $this->query->getModel()->getClient();
    }

    protected function getRoute(string $action, string $method = null): ApiRoute
    {
        $modelClass = $this->query->getModel()::class;

        $route = app(ApiRouter::class)
            ->find($modelClass, $action, $method);

        if (! $route) {
            throw new UnknownRouteException($modelClass, $action, $method);
        }

        return $route;
    }

    protected function prepareUrl(ApiRoute $route): string
    {
        $url = $route->uri;

        foreach ([
            $this->query->getModel()->getKeyName() => $this->query->getModel()->getKey(),
            $this->query->getModel()->foreignKeyName => $this->query->getModel()->foreignKey,
            ...$this->query->getModel()->getParameters(),
            ...$this->parameters,
        ] as $key => $value) {
            if (!$value) {
                continue;
            }

            $url = Str::replace('{'.$key.'}', $value, $url);
        }

        if ($this->query) {
            $url .= $this->buildQuery();
        }

        return $url;
    }

    protected function buildQuery(): string
    {
        $compiler = new ($this->query->getModel()->getConfig()['query']['compiler'])();

        return (string) $compiler->compileQuery($this->query);
    }

    public function withParameters(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }
}
