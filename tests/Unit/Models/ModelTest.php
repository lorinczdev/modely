<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Lorinczdev\Modely\Models\Builder;
use Lorinczdev\Modely\Tests\Mocks\AnotherIntegration\Models\Cat;
use Lorinczdev\Modely\Tests\Mocks\AnotherIntegration\Models\Insects\Spider;
use Lorinczdev\Modely\Tests\Mocks\Integration\ApiClient;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Categories\Category;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Post;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('each model has static variable with config', function () {
    $user = new User();

    expect($user->getConfig())->toBeArray()
        ->and($user->getConfig())->toBe(require __DIR__ . '/../../Mocks/Integration/config.php');
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

it('can be serialized', function () {
    $user = new User(['id' => 1]);

    expect(json_encode($user))->toBe('{"id":1}');
});

it('when registered it prepares models', function () {
    // Models in top directory
    expect((new Cat)->getConfig())->toBeArray()
        ->and((new User)->getConfig())->toBeArray()
        ->and((new Cat)->getConfig())->not()->toBe((new User)->getConfig());

    // Models inside subdirectory
    expect((new Category)->getConfig())->toBeArray()
        ->and((new Spider)->getConfig())->toBeArray()
        ->and((new Spider)->getConfig())->not()->toBe((new Category)->getConfig());
});

it('forwards calls', function () {

})->skip();
