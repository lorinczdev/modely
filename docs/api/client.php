<?php

use Lorinczdev\Modely\Tests\Mocks\Integration\Client;


// Client goal is to send http request with provided data and if nessesary handle authorization
$client = new Client();

// Public methods
$client->get('/users');
$client->post('/users', []);
$client->put('/users', []);
$client->patch('/users', []);
$client->delete('/users', []);

$client->asForm();
$client->asMultipart();

// Authorization
// Can be setup trough authorize() method.

// Creating your own Client
class MyClient extends Client
{
    protected string $baseUrl = 'https://modely-integration.test';

    protected function withConfiguration(\Illuminate\Http\Client\PendingRequest $client)
    {
        // Additional configuration.
    }

    protected function authorize(\Illuminate\Http\Client\PendingRequest $client)
    {
        // Here we can add authorization logic.
    }
}
