<?php

use Illuminate\Http\Client\Request;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('can be save', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/store'))]);

    $user = new User(['name' => 'Marek']);
    $user->save();

    expect($user)
        ->id->toBe(1)
        ->exists->toBe(true);
});

it('sync attributes with originals when saved', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/store'))]);

    $user = new User(['name' => 'Marek']);

    expect($user->getOriginal())->toBeEmpty();

    $user->save();

    expect($user->getOriginal())->toBe(['name' => 'Marek', 'id' => 1]);
});

it('after model is saved the exists property is set to true', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/store'))]);

    $user = new User(['name' => 'Marek']);
    $user->save();

    expect($user->exists)->toBeTrue();
});

it('creates model when saved if exists is equal to false', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/update'))]);

    $user = new User(['name' => 'Marek']);

    expect($user->exists)->toBe(false);

    $user->save();

    Http::assertSent(fn (Request $request) => $request->url() === 'https://modely-integration.test/users');
});

it('updates model when saved if exists is equal to true', function () {
    Http::fake(['*/users/1' => Http::response(body: fixture('Users/update'))]);

    $user = new User(['name' => 'Marek', 'id' => 1]);

    expect($user->exists)->toBe(true);

    $user->save();

    Http::assertSent(fn (Request $request) => $request->url() === 'https://modely-integration.test/users/1');
});

it('has create method', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/store'))]);

    $user = User::create(['name' => 'Marek']);

    expect($user)
        ->toBeInstanceOf(User::class)
        ->id->toBe(1)
        ->exists->toBe(true);
});

it('has update method', function () {
    Http::fake(['*/users/1' => Http::response(body: fixture('Users/update'))]);

    $user = new User(['id' => 1]);

    $updated = $user->update(['name' => 'Keram']);

    expect($user->name)->toBe('Keram')
        ->and($updated)->toBe(true);
});

it('wont be updated when id key is missing', function () {
    Http::fake(['*/users/1' => Http::response(body: fixture('Users/update'))]);

    $user = new User();

    $updated = $user->update(['name' => 'Keram']);

    expect($updated)->toBe(false);
});

it('wont update model when none of the models attributes are dirty', function () {
    Http::fake(['*/users/1' => Http::response(body: fixture('Users/update'))]);

    $user = new User(['name' => 'Marek', 'id' => 1]);
    $user->syncOriginal();
    expect($user->isDirty())->toBe(false);

    $user->update(['name' => 'Marek']);
    Http::assertNotSent(fn (Request $request) => $request->url() === 'https://modely-integration.test/users/1');

    $user->update(['name' => 'Keram']);
    Http::assertSent(fn (Request $request) => $request->url() === 'https://modely-integration.test/users/1');
});

it('exists property gets set to true when created', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/store'))]);

    $user = new User();

    expect($user->exists)->toBe(false);

    $user->save();

    expect($user->exists)->toBe(true);
});

it('gets created on save when exists property is set to false', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/store'))]);

    $user = new User();
    $user->name = 'Marek';

    expect($user->exists)->toBe(false);

    $user->save();

    expect($user->id)->toBe(1);
});

it('gets updated on save when exists property is set to true', function () {
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

it('can get first model or create a new one', function () {
    Http::fake([
        '*/users?name=Marek&limit=1' => Http::sequence()
            ->push(body: fixture('Users/empty'))
            ->push(body: fixture('Users/index')),
        '*/users' => Http::response(body: fixture('Users/store')),
    ]);

    $user = User::firstOrCreate(['name' => 'Marek']);

    expect($user)->toBeInstanceOf(User::class);

    $user = User::query()->firstOrCreate(['name' => 'Marek']);

    expect($user)->toBeInstanceOf(User::class);
});

it('can create or update a model', function () {

    Http::fake([
        '*/users?name=Marek&limit=1' => Http::sequence()
            ->push(body: fixture('Users/empty'))
            ->push(body: fixture('Users/index'))
            ->push(body: fixture('Users/update')),
        '*/users/1' => Http::sequence()
            ->push(body: fixture('Users/update')),
        '*/users' => Http::response(body: fixture('Users/store')),
    ]);

    $user = User::updateOrCreate(['name' => 'Marek']);

    expect($user)->toBeInstanceOf(User::class);

    $user = User::updateOrCreate(['name' => 'Marek'], ['name' => 'keraM']);

    expect($user)
        ->toBeInstanceOf(User::class)
        ->name->toBe('keraM');
});
