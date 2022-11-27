<?php

namespace Lorinczdev\Modely\Tests\Unit\Relations;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Attachment;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Post;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('has morph many relationships', function () {
    Http::fake([
        '*/user/1/attachments' => Http::response(body: fixture('Posts/index')),
    ]);

    $user = new User(['id' => 1]);

    $attachments = $user->attachments()->get();

    expect($attachments)->toBeInstanceOf(Collection::class)
        ->each->toBeInstanceOf(Attachment::class);
});