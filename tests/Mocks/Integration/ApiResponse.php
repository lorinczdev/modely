<?php

namespace Lorinczdev\Modely\Tests\Mocks\Integration;

use Illuminate\Http\File;

class ApiResponse extends \Lorinczdev\Modely\Http\ApiResponse
{
    protected function handleResponse(\Illuminate\Http\Client\Response|\Illuminate\Http\File $response): void
    {
        if ($response instanceof File) {
            $this->data = $response;
            return;
        }

        $this->log($response);

        $data = $response->json();

        $this->data = $data['data'] ?? null;
    }
}
