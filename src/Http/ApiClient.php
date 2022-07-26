<?php

namespace Lorinczdev\Modely\Http;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class ApiClient
{
    protected string $baseUrl;

    protected string $url;

    protected string $method;

    protected ?array $payload;

    protected ?array $multipartPayload = null;

    protected string $contentType = 'json';

    public function get(string $url, array $payload = null): Response|File
    {
        return $this->sendAs('get', ...func_get_args());
    }

    public function post(string $url, array $payload = null): Response|File
    {
        return $this->sendAs('post', ...func_get_args());
    }

    public function put(string $url, array $payload = null): Response|File
    {
        return $this->sendAs('put', ...func_get_args());
    }

    public function patch(string $url, array $payload = null): Response|File
    {
        return $this->sendAs('patch', ...func_get_args());
    }

    public function delete(string $url, array $payload = null): Response
    {
        return $this->sendAs('delete', ...func_get_args());
    }

    public function asForm(): self
    {
        $this->contentType = 'form';

        return $this;
    }

    public function asMultipart(): self
    {
        $this->contentType = 'multipart';

        return $this;
    }

    public function asDownload(): self
    {
        $this->contentType = 'download';

        return $this;
    }

    protected function sendAs(string $method, string $url, array $payload = null): Response|File
    {
        $this->setAction(...func_get_args());

        return $this->send();
    }

    protected function setAction(string $method, string $url, array $payload = null): void
    {
        $this->url = $url;
        $this->method = $method;
        $this->payload = $payload;
    }

    protected function send(): Response|File
    {
        $client = $this->getHttpClient();

        $this->withConfiguration($client);
        $this->authorize($client);

        $payload = $this->preparePayload();

        if ($this->contentType === 'multipart') {
            $client->attach(...$this->multipartPayload);
        }

        if ($this->contentType === 'download') {
            $tempName = tempnam(sys_get_temp_dir(), 'response');
            // $resource = \GuzzleHttp\Psr7\Utils::tryFopen($temp, 'w');
            // $stream = \GuzzleHttp\Psr7\Utils::streamFor($resource);

            $client->sink($tempName);
        }

        $this->log($payload);

        $response = $client->{$this->method}(
            $this->url,
            $payload
        );

        if ($this->contentType === 'download') {
            return new File($tempName);
        }

        return $this->handleResponse($response);
    }

    protected function log(mixed $payload): void
    {
        ray(
            $data = [
                'url' => $this->url,
                'method' => $this->method,
                'payload' => $payload,
                'contentType' => $this->contentType,
            ]
        )
            ->label('Modely')
            ->orange();

        // Log::debug(json_encode($data));
    }

    public function getHttpClient(): PendingRequest
    {
        $client = Http::timeout(10);

        match ($this->contentType) {
            'json' => $client->asJson(),
            'form' => $client->asForm(),
            'multipart' => $client->asMultipart(),
            'download' => '',
        };

        return $client
            ->acceptJson()
            ->withOptions([
                'debug' => false,
            ])
            ->baseUrl($this->baseUrl());
    }

    protected function withConfiguration(PendingRequest $client): void
    {
        // Additional configuration.
    }

    protected function baseUrl(): string
    {
        return $this->baseUrl;
    }

    public function authorize(PendingRequest $client): void
    {
        // Here we can add authorization logic.
    }

    protected function preparePayload(): ?array
    {
        $payload = empty($this->payload) ? null : $this->payload;

        if (! $payload) {
            return null;
        }

        if ($this->contentType === 'multipart') {
            $this->multipartPayload = $this->payload;
            $payload = [];
        }

        return $payload;
    }

    protected function handleResponse(Response $response): Response
    {
        return $response;
    }
}
