<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Categories\Category;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Post;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('works', function () {
    Http::fake([
        '*/users?limit=1' => Http::sequence()
            ->push(body: fixture('Users/index')),
        '*/users/1/posts?limit=1' => Http::sequence()
            ->push(body: fixture('Posts/index')),
        '*/categories?limit=1' => Http::sequence()
            ->push(body: fixture('Posts/index')),
    ]);

    $post = User::first()->posts()->first();

    expect($post->category()->first())->toBeInstanceOf(Category::class);
});
