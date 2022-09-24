<?php

namespace Lorinczdev\Modely\Tests\Mocks\Integration\Models\Categories;

use Lorinczdev\Modely\Models\Model;
use Lorinczdev\Modely\Models\Relations\HasMany;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Post;

class Category extends Model
{
    protected $guarded = [];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
