<?php

use Illuminate\Support\Facades\Http;
use Lorinczdev\Modely\Models\Model;
use Lorinczdev\Modely\Tests\Mocks\Integration\IntegrationQueryBuilder;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Post;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;


it('can read users', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/index'))]);

    $users = User::get();

    expect($users)->toBeCollection();
});

it('can read user detail', function () {
    Http::fake(['*/users/1' => Http::response(body: fixture('Users/show'))]);

    $user = User::find(1);

    expect($user)
        ->toBeInstanceOf(Model::class)
        ->id->toEqual('1')
        ->name->toEqual('Marek');
});

it('can create a user', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/store'))]);

    $user = User::create(['name' => 'Marek']);

    expect($user)
        ->toBeInstanceOf(Model::class)
        ->id->toEqual('1')
        ->name->toEqual('Marek');
});

it('can update a user', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/store'))]);

    $user = User::create(['name' => 'Marek']);

    Http::fake(['*/users/1' => Http::response(body: fixture('Users/update'))]);

    $user->update(['name' => 'keraM']);

    expect($user)->name->toEqual('keraM');
});

it('can delete a user', function () {
    Http::fake(['*/users/1' => Http::response(body: fixture('Users/destroy'))]);

    (new User(['id' => 1]))->delete();

    expect(User::find(1))->toBeNull();
});

it('can read user posts', function () {
    Http::fake([
        '*/users/1' => Http::response(body: fixture('Users/show')),
        '*/users/1/posts' => Http::response(body: fixture('Posts/index'))
    ]);

    $user = User::find(1);

    $posts = $user->posts()->get();

    expect($posts)->toHaveCount(1);
});

it('user can create a post', function () {
    Http::fake([
        '*/users/1' => Http::response(body: fixture('Users/show')),
        '*/users/1/posts' => Http::response(body: fixture('Posts/store'))
    ]);

    $user = User::find(1);

    $post = $user->posts()->create([
        'title' => 'Post A',
    ]);

    expect($post)->toBeInstanceOf(Post::class);
});

test('user can delete all related posts', function () {
    Http::fake([
        '*/users/1' => Http::response(body: fixture('Users/show')),
        '*/users/1/posts/1' => Http::response(body: fixture('Posts/destroy')),
        '*/users/1/posts' => Http::sequence()
            ->push(body: fixture('Posts/index'))
            ->push(body: fixture('Posts/empty'))
    ]);

    $user = User::find(1);

    $user->posts = $user->posts()->get();

    $user->posts()->delete();

    expect($user->posts()->get())->toHaveCount(0);
});


it('has custom query builder', function () {
    $user = new User();

    expect($user->getConfig()['query']['builder'])
        ->toEqual(IntegrationQueryBuilder::class)
        ->and($user->newQuery()->builder())
        ->toBeInstanceOf(IntegrationQueryBuilder::class);
});
