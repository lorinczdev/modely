<?php

use Illuminate\Support\Collection;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('reads collection of models', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/index'))]);

    expect(User::get())->toBeInstanceOf(Collection::class);
});

it('can find a model', function () {
    Http::fake(['*/users/1' => Http::response(body: fixture('Users/show'))]);

    expect(
        User::find(1)
    )
        ->toBeInstanceOf(User::class);
});

it('can get first model', function () {
    Http::fake(['*/users?limit=1' => Http::response(body: fixture('Users/index'))]);

    expect(
        User::query()->first()
    )
        ->toBeInstanceOf(User::class);
});

it('can limit number of returned items', function () {
    Http::fake(['*/users?limit=5' => Http::response(body: fixture('Users/paginate-page-1'))]);

    expect(
        User::query()->take(5)->get()->count()
    )
        ->toBe(5);
});

it('can skip items', function () {
    Http::fake(['*/users?offset=1' => Http::response(body: fixture('Users/empty'))]);

    expect(
        User::skip(1)->get()->isEmpty()
    )
        ->toBeTrue()
        ->and(
            User::offset(1)->get()->isEmpty()
        )
        ->toBeTrue();
});

it('can limit items', function () {
    Http::fake(['*/users?limit=1' => Http::response(body: fixture('Users/index-limit-1'))]);

    expect(
        User::limit(1)->get()
    )
        ->toHaveCount(1);
});

it('has forPage method', function () {
    Http::fake(['*/users?limit=5&offset=5' => Http::response(body: fixture('Users/paginate-page-1'))]);

    expect(
        User::forPage(2, 5)->get()
    )
        ->toHaveCount(5);
});

it('has where method', function () {
    Http::fake(['*/users?name=Marek' => Http::response(body: fixture('Users/index'))]);

    expect(
        User::query()->where('name', 'Marek')->get()->isNotEmpty()
    )
        ->toBeTrue();
});

it('has whereFirst method', function () {
    Http::fake(['*/users?name=Marek&limit=1' => Http::response(body: fixture('Users/index'))]);

    expect(
        User::query()->whereFirst('name', 'Marek')
    )
        ->toBeInstanceOf(User::class);
});

it('can use array in where method', function () {
    Http::fake(['*/users?name=Marek' => Http::response(body: fixture('Users/index'))]);

    expect(
        User::query()->where([['name', 'Marek']])->get()->isNotEmpty()
    )
        ->toBeTrue();
});

it('can order by', function () {
    expect(
        User::query()->orderBy('name')->getQuery()->orders[0]
    )
        ->toHaveKeys(['column', 'direction']);
});

it('query existence of the model', function () {
    Http::fake(['*/users?name=Marek' => Http::response(body: fixture('Users/empty'))]);

    expect(
        User::where('name', 'Marek')->exists()
    )
        ->toBeFalse()
        ->and(
            User::where('name', 'Marek')->doesntExist()
        )
        ->toBeTrue();
});

it('can get fresh model', function () {
    Http::fake(['*/users/1' => Http::response(body: fixture('Users/show'))]);

    $user = new User(['id' => 1]);

    $user = $user->fresh();

    expect($user->name)->toBe('Marek');
});

it('can refresh the model', function () {
    Http::fake(['*/users/1' => Http::response(body: fixture('Users/show'))]);

    $user = new User(['id' => 1]);

    $user->refresh();

    expect($user->name)->toBe('Marek');
});
