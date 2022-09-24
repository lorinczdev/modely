<?php

use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('can be deleted', function () {
    Http::fake(['*/users/1' => Http::response(body: fixture('Users/destroy'))]);

    $user = new User(['id' => 1]);
    $user->delete();

    expect(User::find($user->id))->toBe(null);
});

it('can be destroyed', function () {
    Http::fake([
        '*/users/1' => Http::sequence()
            ->push(body: fixture('Users/show'))
            ->push(body: fixture('Users/destroy')),
    ]);

    $count = User::destroy(1);

    expect($count)->toBe(1);
});

it('can destroy multiple models', function () {
    Http::fake([
        '*/users?id%5B0%5D=1&id%5B1%5D=2&id%5B2%5D=3' => Http::response(body: fixture('Users/index')),
        '*/users/1' => Http::response(body: fixture('Users/destroy')),
        '*/users/2' => Http::response(body: fixture('Users/destroy')),
        '*/users/3' => Http::response(body: fixture('Users/destroy')),
    ]);

    $count = User::destroy(1, 2, 3);

    expect($count)->toBe(3);

    $count = User::destroy([1, 2, 3]);

    expect($count)->toBe(3);
});
