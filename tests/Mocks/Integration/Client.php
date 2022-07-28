<?php

namespace Lorinczdev\Modely\Tests\Mocks\Integration;

use Illuminate\Http\Client\Response;

class Client extends \Lorinczdev\Modely\Http\Client
{
    protected string $baseUrl = 'https://modely-integration.test';

    protected function handleResponse(Response $response): mixed
    {
        $this->logResponse($response);

        $data = $response->json();

        return $data['data'];
    }
}
