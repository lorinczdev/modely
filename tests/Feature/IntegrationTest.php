<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Post;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('can create a user', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/store'))]);

    $user = User::create(['name' => 'Marek']);

    expect($user)
        ->toBeInstanceOf(User::class)
        ->id->toEqual(1)
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
        '*/users/1/posts?title=Post+A' => Http::response(body: fixture('Posts/index')),
    ]);

    $user = User::find(1);

    $posts = $user->posts()->where('title', 'Post A')->get();

    expect($posts)->toHaveCount(1);
});

it('user can create a post', function () {
    Http::fake([
        '*/users/1' => Http::response(body: fixture('Users/show')),
        '*/users/1/posts' => Http::response(body: fixture('Posts/store')),
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
            ->push(body: fixture('Posts/empty')),
    ]);

    $user = User::find(1);

    $user->posts = $user->posts()->get();

    $user->posts()->delete();

    expect($user->posts()->get())->toHaveCount(0);
});

it('user can be promoted', function () {
    Http::fake([
        '*/users/1' => Http::response(body: fixture('Users/show')),
        '*/users/1/promote' => Http::response(body: fixture('Users/show')),
    ]);

    $user = User::find(1);

    $user->promote();

    expect($user)->toBeInstanceOf(User::class)->id->toEqual(1);
});

it('can send a multipart request with file', function () {
    Http::fake();

    $user = new User(['id' => 1]);

    $user->uploadAvatar([
        'name' => 'file',
        'contents' => 'yellow banana',
        'filename' => null,
        'headers' => [],
    ]);

    Http::assertSent(
        fn (Request $request) => $request->isMultipart() && $request->hasFile('file')
    );
});

it('can send request to download a file', function () {
    Http::fake();

    $user = new User(['id' => 1]);

    $response = $user->downloadAvatar();

    expect($response->data())->toBeInstanceOf(\Illuminate\Http\File::class);
});

it('can send a form request', function () {
    Http::fake();

    $user = new User(['id' => 1]);

    $user->options([
        'role' => 'cat',
    ]);

    Http::assertSent(
        fn (Request $request) => $request->isForm() && $request['role'] === 'cat'
    );
});
