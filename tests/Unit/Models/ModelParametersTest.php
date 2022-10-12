<?php

use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('can create a model with parameters', function () {
    $model = User::withParameters([
        'name' => 'John',
        'age' => 30,
    ]);

    expect($model->getParameters())->toBe([
        'name' => 'John',
        'age' => 30,
    ]);
});
