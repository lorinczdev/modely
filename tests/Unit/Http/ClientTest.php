<?php

use Illuminate\Http\Client\PendingRequest;
use Lorinczdev\Modely\Http\ApiClient;


it('uses laravel http client', function () {
    $client = new DummyApiClient();

    expect($client->getHttpClient())->toBeInstanceOf(PendingRequest::class);
});

it('can send GET request', function () {
    Http::fake();

    (new DummyApiClient())->get('users', ['name' => 'Marek']);

    Http::assertSent(function (Illuminate\Http\Client\Request $request) {
        return $request->url() === 'https://example.com/users?name=Marek'
            && $request['name'] === 'Marek'
            && $request->method() === 'GET';
    });
});

it('can send POST request', function () {
    Http::fake();

    (new DummyApiClient())->post('users', ['name' => 'Keram']);

    Http::assertSent(function (Illuminate\Http\Client\Request $request) {
        return $request->url() === 'https://example.com/users'
            && $request['name'] === 'Keram'
            && $request->method() === 'POST';
    });
});

it('can send PUT request', function () {
    Http::fake();

    (new DummyApiClient())->put('users', ['name' => 'Keram']);

    Http::assertSent(function (Illuminate\Http\Client\Request $request) {
        return $request->url() === 'https://example.com/users'
            && $request['name'] === 'Keram'
            && $request->method() === 'PUT';
    });
});

it('can send PATCH request', function () {
    Http::fake();

    (new DummyApiClient())->patch('users', ['name' => 'Keram']);

    Http::assertSent(function (Illuminate\Http\Client\Request $request) {
        return $request->url() === 'https://example.com/users'
            && $request['name'] === 'Keram'
            && $request->method() === 'PATCH';
    });
});

it('can send DELETE request', function () {
    Http::fake();

    (new DummyApiClient())->delete('users/1', []);

    Http::assertSent(function (Illuminate\Http\Client\Request $request) {
        return $request->url() === 'https://example.com/users/1'
            && $request->method() === 'DELETE';
    });
});

it('can send request as form', function () {
    Http::fake();

    (new DummyApiClient())->asForm()->post('users/1');

    Http::assertSent(function (Illuminate\Http\Client\Request $request) {
        return $request->isForm();
    });
});

it('can send request as multi-part', function () {
    Http::fake();

    (new DummyApiClient())
        ->asMultipart()
        ->post('users/1', ['name' => 'file', 'contents' => 'Hello World']);

    Http::assertSent(function (Illuminate\Http\Client\Request $request) {
        return $request->isMultipart();
    });
});

it('can set a action', function () {
    $client = new DummyApiClient();

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

class DummyApiClient extends ApiClient
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
}
