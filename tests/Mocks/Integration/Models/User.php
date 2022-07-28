<?php

namespace Lorinczdev\Modely\Tests\Mocks\Integration\Models;

use Illuminate\Support\Collection;
use Lorinczdev\Modely\Models\Model;
use Lorinczdev\Modely\Models\Relations\HasMany;

/**
 * @property Collection<int, Post> $posts // TODO how to make this work?
 */
class User extends Model
{
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
