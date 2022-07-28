<?php

use Lorinczdev\Modely\Routing\Route as ApiRoute;
use Lorinczdev\Modely\Routing\RouteResource as BaseRouteResource;
use Lorinczdev\Modely\Routing\RouteResourceOptions;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

beforeEach(function () {
    App::forgetInstance(ApiRoute::class);

    app(ApiRoute::class)->use('integration');
});

it('can be created with a url and a model', function () {
    $routeResource = new RouteResource('/user', User::class);

    expect($routeResource)->toBeInstanceOf(RouteResource::class);
});

it('can select only specific actions', function () {
    $routeResource = (new RouteResource('/user', User::class))->only('index');

    expect($routeResource->actions)->toEqual(['index']);
});

it('can exclude actions', function () {
    $routeResource = (new RouteResource('/user', User::class))->except('index');

    expect($routeResource->actions)->toEqual(['show', 'store', 'update', 'destroy']);
});

it('can add additional actions', function () {
    RouteResourceOptions::addAction('updateMany', [
        'method' => 'put',
        'url' => 'users',
    ]);

    $routeResource = (new RouteResource('/user', User::class))
        ->additionalActions('updateMany');

    expect($routeResource->actions)->toEqual(['index', 'show', 'store', 'update', 'destroy', 'updateMany']);
});

it('can be compiled', function () {
    $routeResource = (new RouteResource('/user', User::class));

    $routeResource->compile();

    expect(
        app(ApiRoute::class)
            ->routesByIntegration('integration')
    )->toHaveCount(5);

});

class RouteResource extends BaseRouteResource
{
    public array $actions = [
        'index', 'show', 'store', 'update', 'destroy',
    ];
}
