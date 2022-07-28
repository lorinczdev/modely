<?php

namespace Lorinczdev\Modely\Http;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

class Response implements Arrayable, ArrayAccess
{
    public function __construct(protected ?array $data = null)
    {
        //
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return $this->getData();
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
