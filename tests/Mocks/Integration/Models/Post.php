<?php

namespace Lorinczdev\Modely\Tests\Mocks\Integration\Models;

use Lorinczdev\Modely\Models\Model;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Categories\Category;

class Post extends Model
{
    protected $guarded = [];

    public function user(): \Lorinczdev\Modely\Models\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): \Lorinczdev\Modely\Models\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
