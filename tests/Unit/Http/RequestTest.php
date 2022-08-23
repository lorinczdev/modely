<?php

use Lorinczdev\Modely\Http\ApiRequest;
use Lorinczdev\Modely\Routing\ApiRoute;
use Lorinczdev\Modely\Routing\UnknownRouteException;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('can be initialized statically', function () {
    expect(
        DummyApiRequest::use(User::query()->getQuery())
    )
        ->toBeInstanceOf(ApiRequest::class);
});

it('can send a request', function () {
    \Illuminate\Support\Facades\Http::fake([
        '*/users' => \Illuminate\Support\Facades\Http::response(fixture('Users/index')),
    ]);

    $response = DummyApiRequest::use(User::query()->getQuery())->send('index');

    expect($response)
        ->toBeInstanceOf(\Lorinczdev\Modely\Http\ApiResponse::class);
});

it('throws exception when route was not registered', function () {
    DummyApiRequest::use(User::query()->getQuery())->send('terminate');
})->throws(UnknownRouteException::class);

it('allows to pass extra parameters', function () {
    $request = DummyApiRequest::use(User::query()->getQuery())->withParameters(['hello' => 'world']);

    expect($request->parameters)
        ->toBe(['hello' => 'world']);
});

it('prepares url', function () {
    $user = new User((['id' => 1]));

    $request = DummyApiRequest::use($user->newQuery()->getQuery())
        ->withParameters(['color' => 'yellow']);

    $route = new ApiRoute('get', 'users/{id}/{color}', 'changeColor');

    expect($request->prepareUrl($route))
        ->toBe('users/1/yellow');
});

it('prepares http query string', function () {

    expect(
        DummyApiRequest::use(
            User::query()->getQuery()->where('name', 'Marek')
        )
    )
        ->buildQuery()
        ->toBe('?name=Marek');
});

class DummyApiRequest extends Lorinczdev\Modely\Http\ApiRequest
{
    public array $parameters = [];

    public function prepareUrl(ApiRoute $route): string
    {
        return parent::prepareUrl($route);
    }

    public function buildQuery(): string
    {
        return parent::buildQuery();
    }
}
