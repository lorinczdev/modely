<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Lorinczdev\Modely\Models\Builder;
use Lorinczdev\Modely\Tests\Mocks\Integration\ApiClient;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Post;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('each model has static variable with config', function () {
    $user = new User();

    expect($user::$config)->toBeArray()
        ->and($user::$config)->toBe(require __DIR__ . '/../../Mocks/Integration/config.php');
});

it('can get client', function () {
    $user = new User();

    $client = $user->getClient();

    expect($client)->toBeInstanceOf(ApiClient::class);
});

it('can get config', function () {
    $user = new User();

    $config = $user->getConfig();

    expect($config)->toBeArray()
        ->and($config)->toBe(require __DIR__ . '/../../Mocks/Integration/config.php');
});

it('can get new instance of builder', function () {
    $user = new User();

    $query = $user->newQuery();

    expect($query)
        ->toBeInstanceOf(Builder::class)
        ->and(User::query())
        ->toBeInstanceOf(Builder::class);
});

it('it maps properties with relation model when property name and defined relationship match', function () {
    $user = new User([
        'id' => 1,
        'posts' => [
            ['id' => 1, 'title' => 'Post A']
        ]
    ]);

    expect($user->posts)
        ->toBeInstanceOf(Collection::class)
        ->and($user->posts->first())->toBeInstanceOf(Post::class);
});

it('can call a dynamic endpoint trough magic method', function () {
    Http::fake();

    $user = new User(['id' => 1]);

    $user->promote();

    Http::assertSent(fn (Request $request) => $request->url() === 'https://modely-integration.test/users/1/promote'
    );
});

it('can execute any registered route', function () {
    Http::fake();

    $user = new User(['id' => 1]);

    $user->execute('promote');

    Http::assertSent(fn (Request $request) => $request->url() === 'https://modely-integration.test/users/1/promote'
    );
});

it('forwards calls', function () {

})->skip();
