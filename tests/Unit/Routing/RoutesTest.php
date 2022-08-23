<?php

use Lorinczdev\Modely\Facades\ApiRoute;
use Lorinczdev\Modely\Routing\ApiRouter;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

beforeEach(function () {
    App::forgetInstance(ApiRouter::class);
    ApiRoute::refresh();

    ApiRoute::setIntegration('integration');
});

it('can register GET route', function () {
    ApiRoute::get('users', [User::class, 'index']);

    expect(
        hasRoute(model: User::class, method: 'GET', uri: 'users', action: 'index')
    )->toBe(true);
});

it('can register POST route', function () {
    ApiRoute::post('users', [User::class, 'store']);

    expect(
        hasRoute(model: User::class, method: 'POST', uri: 'users', action: 'store')
    )->toBe(true);
});

it('can register PATCH route', function () {
    ApiRoute::patch('users', [User::class, 'update']);

    expect(
        hasRoute(model: User::class, method: 'PATCH', uri: 'users', action: 'update')
    )->toBe(true);
});

it('can register PUT route', function () {
    ApiRoute::put('users', [User::class, 'update']);

    expect(
        hasRoute(model: User::class, method: 'PUT', uri: 'users', action: 'update')
    )->toBe(true);
});

it('can register DELETE route', function () {
    ApiRoute::delete('users/{id}', [User::class, 'destroy']);

    expect(
        hasRoute(model: User::class, method: 'DELETE', uri: 'users/{id}', action: 'destroy')
    )->toBe(true);
});

it('can register routes under a group', function () {
    ApiRoute::group(['prefix' => 'users'], function () {
        ApiRoute::get('/', [User::class, 'index']);
    });

    expect(
        hasRoute(model: User::class, method: 'GET', uri: 'users', action: 'index')
    )->toBe(true);
});

it('can change content type', function () {
    $route = ApiRoute::post('users', [User::class, 'store']);
    expect($route->contentType)->toBe('json');

    $route->asForm();
    expect($route->contentType)->toBe('form');

    $route->asMultipart();
    expect($route->contentType)->toBe('multipart');
});

it('can compile routes', function () {
    ApiRoute::resource('users', User::class);

    ApiRoute::compile();

    expect(
        ApiRoute::getCompiledRoutesByIntegration('integration')
    )->toHaveCount(5);
});

it('can create a route resource', function () {
    ApiRoute::resource('users', User::class);

    expect(
        ApiRoute::getRoutesByIntegration('integration')
            ->filter(fn ($route) => $route instanceof \Lorinczdev\Modely\Routing\ApiRouteResource)
            ->isNotEmpty()
    )->toBe(true);
});

it('can find a route', function () {
    ApiRoute::get('users', [User::class, 'index']);

    ApiRoute::compile();

    $route = ApiRoute::find(User::class, 'index');

    expect($route)
        ->toBeInstanceOf(\Lorinczdev\Modely\Routing\ApiRoute::class)
        ->not()->toBeNull();

    $route = ApiRoute::find(User::class, 'index', 'get');

    expect($route)
        ->toBeInstanceOf(\Lorinczdev\Modely\Routing\ApiRoute::class)
        ->not()->toBeNull();
});

it('can set and get name of the integration', function () {
    ApiRoute::setIntegration('test');

    expect(ApiRoute::getIntegration())->toBe('test');
});

function hasRoute(
    string $model,
    string $method,
    string $uri,
    string $action,
    string $integration = 'integration'
): bool
{
    return ApiRoute::getRoutesByIntegration($integration)
        ->where('model', $model)
        ->where('method', $method)
        ->where('action', $action)
        ->where('uri', $uri)
        ->isNotEmpty();
}
