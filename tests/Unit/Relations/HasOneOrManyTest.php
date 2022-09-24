<?php

use Illuminate\Support\Collection;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Post;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('can create a model');

it('can delete a model');

it('can get models');

it('can get first model', function () {
    Http::fake([
        '*/users/1/posts?limit=1' => Http::response(body: fixture('Posts/index')),
    ]);

    $user = new User(['id' => 1]);

    $post = $user->posts()->first();

    expect($post)->toBeInstanceOf(Post::class);
});

it('can transform array of items to models', function () {
    $user = new User(['id' => 1]);

    $posts = $user->posts()->fill([
        ['id' => 1, 'title' => 'Post A'],
    ]);

    expect($posts)->toBeInstanceOf(Collection::class);
});

it('has where clause', function () {
    Http::fake([
        '*/users/1/posts?title=Post+A' => Http::response(body: fixture('Posts/index')),
    ]);

    $user = new User(['id' => 1]);

    $posts = $user->posts()->where('title', 'Post A')->get();

    expect($posts)->toBeInstanceOf(Collection::class)
        ->first()->toBeInstanceOf(Post::class);
});
