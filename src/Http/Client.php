<?php

namespace Lorinczdev\Modely\Http;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

abstract class Client
{
    protected string $baseUrl;

    protected string $url;

    protected string $method;

    protected ?array $payload;

    protected function getHttpClient(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout(30)
            ->withOptions([
                'debug' => false,
            ])
            ->baseUrl($this->baseUrl);
    }

    public function get(string $url, ?array $payload = null)
    {
        return $this->sendAs('get', ...func_get_args());
    }

    public function post(string $url, array $payload)
    {
        return $this->sendAs('post', ...func_get_args());
    }

    public function put(string $url, array $payload)
    {
        return $this->sendAs('put', ...func_get_args());
    }

    public function patch(string $url, array $payload)
    {
        return $this->sendAs('patch', ...func_get_args());
    }

    public function delete(string $url, array $payload)
    {
        return $this->sendAs('delete', ...func_get_args());
    }

    protected function send()
    {
        $response = $this->getHttpClient()->{$this->method}(
            $this->url,
            empty($this->payload) ? null : $this->payload
        );

        return $this->handleResponse($response);
    }

    protected function contentTypeIsJson(Response $response): bool
    {
        return Str::startsWith($response->header('Content-Type'), 'application/json');
    }

    protected function logResponse(Response $response): void
    {
        if ($response->failed()) {
            ray(
                [
                    'status' => 'Failure',
                    'method' => $this->method,
                    'url' => $this->url,
                    'payload' => $this->payload,
                    'response' => $response->json(),
                    'full_response' => $response,
                ]
            )
                ->red();

            $response->throw();
        }

        ray(
            [
                'status' => 'Success',
                'method' => $this->method,
                'url' => $this->url,
                'payload' => $this->payload,
                'response' => $response->json(),
            ]
        )
            ->green();
    }

    protected function handleResponse(Response $response): mixed
    {
        $this->logResponse($response);

        if ($this->contentTypeIsJson($response)) {
            $data = $response->json();
        } else {
            $data = $response->getBody()->getContents();

            ray()->image(base64_encode($data));
        }

        return $data;
    }

    protected function setAction(string $method, string $url, array $payload = null): void
    {
        $this->url = $url;
        $this->method = $method;
        $this->payload = $payload;
    }

    protected function sendAs(string $method, string $url, array $payload = null)
    {
        $this->setAction(...func_get_args());

        return $this->send();
    }
}
