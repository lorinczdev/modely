<?php

namespace Lorinczdev\Modely\Http;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

class ApiResponse implements Arrayable, ArrayAccess
{
    protected ?array $data = null;

    public function __construct(protected \Illuminate\Http\Client\Response $response)
    {
        $this->log();

        $this->handleResponse($response);
    }

    protected function handleResponse(\Illuminate\Http\Client\Response $response): void
    {
        if ($this->isJson()) {
            $data = $response->json();
        } else {
            $data = $response->getBody()->getContents();

            ray()->image(base64_encode($data));
        }

        $this->data = $data;
    }

    protected function log(): void
    {
        ray(
            [
                'status' => $this->response->failed() ? 'Failure' : 'Success',
                'contents' => $this->response->json(),
                'response' => $this->response,
            ]
        )
            ->label('Modely')
            ->color($this->response->failed() ? 'red' : 'green');

        if ($this->response->failed()) {
            $this->response->throw();
        }
    }

    public function isJson(): bool
    {
        return Str::startsWith($this->response->header('Content-Type'), 'application/json');
    }

    public function toArray(): array
    {
        return $this->data();
    }

    public function data(): ?array
    {
        return $this->data;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }
}
