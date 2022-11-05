<?php

use Lorinczdev\Modely\Facades\ApiRoute;
use Lorinczdev\Modely\Routing\ApiRouter;
use Lorinczdev\Modely\Routing\ApiRouteResource as BaseApiRouteResource;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

beforeEach(function () {
    App::forgetInstance(ApiRouter::class);
    ApiRoute::refresh();

    ApiRoute::setIntegration('integration');
});

it('can be created with a url and a model', function () {
    $routeResource = new ApiRouteResource('/user', User::class);

    expect($routeResource)->toBeInstanceOf(ApiRouteResource::class);
});

it('can select only specific actions', function () {
    $routeResource = (new ApiRouteResource('/user', User::class))->only('index');

    expect($routeResource->options['only'])->toEqual(['index']);
});

it('can exclude actions', function () {
    $routeResource = (new ApiRouteResource('/user', User::class))->except('index');

    expect($routeResource->options['except'])->toEqual(['index']);

    expect($routeResource->getActions())->not->toContain('index');
});

it('can add additional actions', function () {
    $routeResource = (new ApiRouteResource('/user', User::class))
        ->addAction('updateMany');

    expect($routeResource->actions)->toEqual(['index', 'show', 'store', 'update', 'destroy', 'updateMany']);
});

it('can be compiled', function () {
    ApiRoute::get('/{model}/promote', 'promote')->asAction()->partOf('promoting');
    ApiRoute::get('/{model}/demote', 'demote')->asAction()->partOf('promoting');

    $routeResource = (new ApiRouteResource('/user', User::class))
        ->addActionGroup('promoting');

    $routeResource->compile(
        ApiRoute::getRoutesByIntegration('integration')
    );

    expect(
        ApiRoute::getRoutesByIntegration('integration')
            ->where('reusableAction', false)
    )->toHaveCount(7);
});

class ApiRouteResource extends BaseApiRouteResource
{
    public array $options = [];

    public array $actions = [
        'index', 'show', 'store', 'update', 'destroy',
    ];
}
