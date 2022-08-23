<?php

use Lorinczdev\Modely\Models\Pagination\PaginateCollection;
use Lorinczdev\Modely\Models\Pagination\Pagination;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

beforeEach(function () {
    Http::fake(['*/users?limit=5' => Http::response(body: fixture('Users/paginate-page-1'))]);
    Http::fake(['*/users?limit=5&offset=5' => Http::response(body: fixture('Users/paginate-page-2'))]);
});

it('sets limit on query when initialized', function () {
    new Pagination($query = User::query(), 5, 1);

    expect($query->getQuery()->limit)->toBe(5);
});

it('fetches data when initialized', function () {
    $pagination = new Pagination(User::query(), 5, 1);

    expect($pagination->getCollection())->toHaveCount(5);
});

it('can fetch next page', function () {
    $pagination = new Pagination(User::query(), 5, 1);

    expect($pagination->getCollection())->toHaveCount(5);

    $pagination->next();

    expect($pagination->getCollection())->toHaveCount(2);
});

it('can fetch previous page', function () {
    $pagination = new Pagination(User::query(), 5, 2);

    expect($pagination->getCollection())->toHaveCount(2);

    $pagination->previous();

    expect($pagination->getCollection())->toHaveCount(5);
});

it('can check if current page is last', function () {
    $pagination = new Pagination(User::query(), 5, 2);

    expect($pagination->isLastPage())->toBeTrue();
});

it('can fetch more items', function () {
    $pagination = new Pagination(User::query(), 5, 1);

    expect($pagination->getCollection())->toHaveCount(5);

    $pagination->more();

    expect($pagination->getCollection())->toHaveCount(7);
});

it('can fetch all items', function () {
    $pagination = new Pagination(User::query(), 5, 1);

    expect($pagination->fetchAll()->getCollection())->toHaveCount(7);
});

it('can fetch all items and returns collection', function () {
    $pagination = new Pagination(User::query(), 5, 1);

    expect($pagination->getAll())->toBeInstanceOf(PaginateCollection::class)->toHaveCount(7);
});

it('can get collection', function () {
    $pagination = new Pagination(User::query(), 5, 1);

    expect($pagination->getCollection())->toBeInstanceOf(PaginateCollection::class);
});

it('has method untilLast', function () {
    $pagination = new Pagination(User::query(), 5, 1);

    $pagination->untilLast(fn ($user) => expect($user)->toBeInstanceOf(User::class));
});

it('is arrayable', function () {
    $pagination = new Pagination(User::query(), 5, 1);

    expect($pagination->toArray())->toBeArray();
});
