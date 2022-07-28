<?php

use Illuminate\Support\Collection;
use Lorinczdev\Modely\Models\Query;
use Lorinczdev\Modely\Tests\Mocks\Integration\Client;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Post;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('can get client', function () {
    $user = new User();

    $client = $user->getClient();

    expect($client)->toBeInstanceOf(Client::class);
});

it('can get new instance of query', function () {
    $user = new User();

    $query = $user->newQuery();

    expect($query)
        ->toBeInstanceOf(Query::class)
        ->and(User::query())
        ->toBeInstanceOf(Query::class);
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

it('exists property gets set to true when created', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/store'))]);

    $user = new User();

    expect($user->exists)->toBe(false);

    $user->save();

    expect($user->exists)->toBe(true);
});

it('when initialized with primary key the exists property is set to true', function () {
    $user = new User(['id' => 1]);

    expect($user->exists)->toBe(true);

    $user = new User();

    expect($user->exists)->toBe(false);
});

it('gets created when saved and exists is set to false', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/store'))]);

    $user = new User();
    $user->save();

    expect($user->id)->toBe(1);
});

it('gets updated when saved and exists is set to true', function () {
    Http::fake(['*/users/1' => Http::response(body: fixture('Users/update'))]);

    $user = new User(['id' => 1]);
    $user->name = 'keraM';
    $user->save();

    expect($user->name)->toBe('keraM');
});

it('can be update', function () {
    Http::fake(['*/users/1' => Http::response(body: fixture('Users/update'))]);

    $user = new User(['id' => 1]);
    $user->update(['name' => 'keraM']);

    expect($user->name)->toBe('keraM');
});

it('can be deleted', function () {
    Http::fake(['*/users/1' => Http::response(body: fixture('Users/destroy'))]);

    $user = new User(['id' => 1]);
    $user->delete();

    expect(User::find($user->id))->toBe(null);
});

it('can create a collection of models', function () {
    $usersArray = fixture('Users/index')['data'];

    $users = (new User)->newCollection($usersArray);

    expect($users)
        ->toBeInstanceOf(Collection::class)
        ->and($users->first())->toBeInstanceOf(User::class);
});
