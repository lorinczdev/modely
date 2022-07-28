<?php

use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Lorinczdev\Modely\Http\Client;


it('has default http client builder', function () {
    $client = new DummyClient();

    expect($client->getHttpClient())->toBeInstanceOf(PendingRequest::class);
});

it('can send GET request', function () {
    Http::fake();

    (new DummyClient())->get('users');

    Http::assertSent(function (Illuminate\Http\Client\Request $request) {
        return $request->url() === 'https://example.com/users'
            && $request->method() === 'GET';
    });
});

it('can send POST request', function () {
    Http::fake();

    (new DummyClient())->post('users', []);

    Http::assertSent(function (Illuminate\Http\Client\Request $request) {
        return $request->url() === 'https://example.com/users'
            && $request->method() === 'POST';
    });
});

it('can send PUT request', function () {
    Http::fake();

    (new DummyClient())->put('users', []);

    Http::assertSent(function (Illuminate\Http\Client\Request $request) {
        return $request->url() === 'https://example.com/users'
            && $request->method() === 'PUT';
    });
});

it('can send PATCH request', function () {
    Http::fake();

    (new DummyClient())->patch('users', []);

    Http::assertSent(function (Illuminate\Http\Client\Request $request) {
        return $request->url() === 'https://example.com/users'
            && $request->method() === 'PATCH';
    });
});

it('can send DELETE request', function () {
    Http::fake();

    (new DummyClient())->delete('users/1', []);

    Http::assertSent(function (Illuminate\Http\Client\Request $request) {
        return $request->url() === 'https://example.com/users/1'
            && $request->method() === 'DELETE';
    });
});

it('can check if content type is json', function () {
    $client = new DummyClient();

    expect(
        $client->contentTypeIsJson(
            new Response(response: new Psr7Response(200, ['Content-Type' => 'application/json'], null))
        )
    )->toBe(true);
});

it('logs response', function () {
    $client = new DummyClient();

    $client->setAction(
        method: 'get',
        url: 'users',
        payload: null
    );

    $client->logResponse(
        new Response(
            response: new Psr7Response(500, ['Content-Type' => 'application/json'], null)
        )
    );
})->throws(RequestException::class);

it('handles response', function () {
    $client = new DummyClient();

    $client->setAction(
        method: 'get',
        url: 'users',
        payload: null
    );

    $user = [
        'id' => 1,
        'name' => 'Marek',
    ];

    expect(
        $client->handleResponse(
            new Response(
                response: new Psr7Response(
                    200,
                    ['Content-Type' => 'application/json'],
                    json_encode($user, JSON_THROW_ON_ERROR)
                )
            )
        )
    )->toBe($user);
});

it('can set a action', function () {
    $client = new DummyClient();

    $client->setAction(
        method: 'get',
        url: 'users',
        payload: null
    );

    expect($client)
        ->url->toBe('users')
        ->method->toBe('get')
        ->payload->toBe(null);
});

class DummyClient extends Client
{
    protected string $baseUrl = 'https://example.com';

    public string $url;

    public string $method;

    public ?array $payload;

    public function getHttpClient(): PendingRequest
    {
        return parent::getHttpClient();
    }

    public function setAction(string $method, string $url, array $payload = null): void
    {
        parent::setAction($method, $url, $payload);
    }

    public function contentTypeIsJson(Response $response): bool
    {
        return parent::contentTypeIsJson($response);
    }

    public function logResponse(Response $response): void
    {
        parent::logResponse($response);
    }

    public function handleResponse(Response $response): mixed
    {
        return parent::handleResponse($response);
    }
}
