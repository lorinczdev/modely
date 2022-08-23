<?php

namespace Lorinczdev\Modely\Tests\Mocks\Integration;

class ApiResponse extends \Lorinczdev\Modely\Http\ApiResponse
{
    protected function handleResponse(\Illuminate\Http\Client\Response $response): void
    {
        $this->log($response);

        $data = $response->json();

        $this->data = $data['data'] ?? null;
    }
}
