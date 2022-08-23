<?php

use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('can get model instance', function () {
    $query = User::query();

    expect($query->getModel())->toBeInstanceOf(User::class);
});

it('can paginate', function () {
    Http::fake(['*/users?limit=15' => Http::response(body: fixture('Users/paginate-page-1'))]);

    expect(
        User::paginate()
    )
        ->toBeInstanceOf(Lorinczdev\Modely\Models\Pagination\Pagination::class);
});
