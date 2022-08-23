<?php

use Illuminate\Support\Facades\Http;
use Lorinczdev\Modely\Models\Pagination\PaginateCollection;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('can read users', function () {
    Http::fake(['*/users' => Http::response(body: fixture('Users/index'))]);

    $users = User::get();

    expect($users)
        ->toBeCollection()
        ->first()->toBeInstanceOf(User::class)
        ->first()->id->toBe(1);

    $users = User::all();

    expect($users)
        ->toBeCollection()
        ->first()->toBeInstanceOf(User::class)
        ->first()->id->toBe(1);
});

it('can filter results', function () {

    Http::fake(['*/users?name=Marek' => Http::response(body: fixture('Users/index'))]);

    $users = User::where('name', 'Marek')->get();

    expect($users)
        ->toBeCollection()
        ->first()->toBeInstanceOf(User::class)
        ->first()->id->toBe(1);
});

it('can paginate', function () {
    Http::fake(['*/users?limit=10' => Http::response(body: fixture('Users/paginate-page-1'))]);

    $users = User::paginate(10);

    expect($users)
        ->toBeInstanceOf(Lorinczdev\Modely\Models\Pagination\Pagination::class)
        ->getCollection()->toBeInstanceOf(PaginateCollection::class)
        ->getCollection()->first()->toBeInstanceOf(User::class)
        ->getCollection()->first()->id->toBe(1);
});

it('can read user detail', function () {
    Http::fake(['*/users/1' => Http::response(body: fixture('Users/show'))]);

    $user = User::find(1);

    expect($user)
        ->toBeInstanceOf(User::class)
        ->id->toBe(1)
        ->name->toBe('Marek');
});
