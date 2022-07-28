<?php

use Lorinczdev\Modely\Routing\Route as ApiRoute;
use Lorinczdev\Modely\Routing\RouteResourceOptions;

beforeEach(function () {
    app(ApiRoute::class)->use('integration');
});

it('can read and add groups', function () {
    RouteResourceOptions::addGroup('users', [
        RouteResourceOptions::addAction('createMany', [
            'method' => 'post',
            'url' => 'many',
        ]),
    ]);

    expect(app(RouteResourceOptions::class)->getGroups()['users'][0])->toEqual('createMany');
});

it('can read and add actions', function () {
    RouteResourceOptions::addAction('updateMany', [
        'method' => 'put',
        'url' => 'many',
    ]);

    expect(app(RouteResourceOptions::class)->getActions())->toHaveKey('updateMany');
});
