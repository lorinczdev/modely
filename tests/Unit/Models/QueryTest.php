<?php

use Illuminate\Support\Collection;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('can read models', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/index'))]);

    $query = User::query();

    expect($query->get())->toBeInstanceOf(Collection::class);
});

it('can get model instance', function () {
    $query = User::query();

    expect($query->getModel())->toBeInstanceOf(User::class);
});

it('can create a model', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/store'))]);

    expect(
        User::query()->create(['name' => 'Marek'])
    )
        ->toBeInstanceOf(User::class);
});

it('can find a model', function () {
    Http::fake(['*/users/1' => Http::response(body: fixture('Users/show'))]);

    expect(
        User::query()->find(1)
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

it('can limit items', function () {
    Http::fake(['*/users?limit=0' => Http::response(body: fixture('Users/empty'))]);

    expect(
        User::query()->take(0)->get()->isEmpty()
    )
        ->toBeTrue();
});

it('can skip items', function () {
    Http::fake(['*/users?offset=1' => Http::response(body: fixture('Users/empty'))]);

    expect(
        User::query()->skip(1)->get()->isEmpty()
    )
        ->toBeTrue();
});

it('has where clause', function () {
    Http::fake(['*/users?name=Marek' => Http::response(body: fixture('Users/index'))]);

    expect(
        User::query()->where('name', 'Marek')->get()->isNotEmpty()
    )
        ->toBeTrue();
});

it('has whereFirst clause', function () {
    Http::fake(['*/users?name=Marek&limit=1' => Http::response(body: fixture('Users/index'))]);

    expect(
        User::query()->whereFirst('name', 'Marek')
    )
        ->toBeInstanceOf(User::class);
});

it('can paginate', function () {
    Http::fake(['*/users?page=1&limit=15' => Http::response(body: fixture('Users/paginate-page-1'))]);

    expect(
        User::query()->paginate()
    )
        ->toBeInstanceOf(Lorinczdev\Modely\Models\Pagination\Pagination::class);
});

it('can set page', function () {
    $query = User::query()->page(5);

    expect(
        $query->query['page'][2]
    )
        ->toBe(5);
});

it('can order by', function () {
    expect(
        User::query()->orderBy('name')
    )
        ->query
        ->toHaveKeys(['sortColumn', 'sortDirection']);
});

it('can add query manually', function () {
    expect(
        User::query()->addQuery('name', 'Marek')
    )
        ->query
        ->toBe([
            'name' => [
                'name',
                '',
                'Marek',
            ],
        ]);
});


it('can get first model or create a new one', function () {
    Http::fake([
        '*/users?name=Marek&limit=1' => Http::sequence()
            ->push(body: fixture('Users/empty'))
            ->push(body: fixture('Users/index')),
        '*/users' => Http::response(body: fixture('Users/store')),
    ]);

    $user = User::query()->firstOrCreate(['name' => 'Marek']);

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

    $user = User::query()->createOrUpdate(['name' => 'Marek']);

    expect($user)->toBeInstanceOf(User::class);

    $user = User::query()->createOrUpdate(['name' => 'Marek'], ['name' => 'keraM']);

    expect($user)
        ->toBeInstanceOf(User::class)
        ->name->toBe('keraM');
});
