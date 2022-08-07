<?php

use Lorinczdev\Modely\Models\Relations\HasMany;
use Lorinczdev\Modely\Models\Relations\HasOne;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Post;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('can get foreign key name', function () {
    $user = new DummyUser();

    expect($user->getForeignKey())->toBe('dummy_user_id');
});

it('has hasOne relation', function () {
    $user = new DummyUser(['id' => 1]);

    expect($user->hasOne(Post::class))->toBeInstanceOf(HasOne::class);
});

it('has hasMany relation', function () {
    $user = new DummyUser(['id' => 1]);

    expect($user->hasMany(Post::class))->toBeInstanceOf(HasMany::class);
});


class DummyUser extends User
{
    public function hasMany(string $className, ?string $foreignKey = null, ?string $localKey = null): HasMany
    {
        return parent::hasMany($className);
    }

    public function hasOne(string $className, ?string $foreignKey = null, ?string $localKey = null): HasOne
    {
        return parent::hasOne($className);
    }
}
