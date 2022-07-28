<?php

use Lorinczdev\Modely\Routing\Route as ApiRoute;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

beforeEach(function () {
    App::forgetInstance(ApiRoute::class);

    app(ApiRoute::class)->use('integration');
});

it('can register GET route', function () {
    ApiRoute::get('users', [User::class, 'index']);

    expect(
        hasRoute(model: User::class, method: 'GET', url: 'users', action: 'index')
    )->toBe(true);
});

it('can register POST route', function () {
    ApiRoute::post('users', [User::class, 'store']);

    expect(
        hasRoute(model: User::class, method: 'POST', url: 'users', action: 'store')
    )->toBe(true);
});

it('can register PATCH route', function () {
    ApiRoute::patch('users', [User::class, 'update']);

    expect(
        hasRoute(model: User::class, method: 'PATCH', url: 'users', action: 'update')
    )->toBe(true);
});

it('can register PUT route', function () {
    ApiRoute::put('users', [User::class, 'update']);

    expect(
        hasRoute(model: User::class, method: 'PUT', url: 'users', action: 'update')
    )->toBe(true);
});

it('can register DELETE route', function () {
    ApiRoute::delete('users/{id}', [User::class, 'destroy']);

    expect(
        hasRoute(model: User::class, method: 'DELETE', url: 'users/{id}', action: 'destroy')
    )->toBe(true);
});

it('can compile routes', function () {
    ApiRoute::resource('users', User::class);

    app(ApiRoute::class)->compile();

    expect(
        app(ApiRoute::class)
            ->compiledRoutesByIntegration('integration')
    )->toHaveCount(5);
});

it('can create a route resource', function () {
    ApiRoute::resource('users', User::class);

    expect(
        app(ApiRoute::class)
            ->routesByIntegration('integration')
            ->filter(fn($route) => $route instanceof \Lorinczdev\Modely\Routing\RouteResource)
            ->isNotEmpty()
    )->toBe(true);
});

it('can find a route', function () {
    ApiRoute::get('users', [User::class, 'index']);

    app(ApiRoute::class)->compile();

    $route = app(ApiRoute::class)->find(User::class, 'index');

    expect($route)->not()->toBeNull();
});

function hasRoute(string $model, string $method, string $url, string $action): bool
{
    return app(ApiRoute::class)
        ->routesByIntegration('integration')
        ->where('model', $model)
        ->where('method', $method)
        ->where('action', $action)
        ->where('url', $url)
        ->isNotEmpty();
}
