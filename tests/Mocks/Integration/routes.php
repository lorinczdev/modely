<?php

use Lorinczdev\Modely\Facades\ApiRoute;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Categories\Category;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Post;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

ApiRoute::post('/{model}/lock', 'lock')->asAction()->partOf('locking');

ApiRoute::resource('users', User::class)->addActionGroup('locking');

ApiRoute::put('users/{id}/promote', [User::class, 'promote']);

ApiRoute::post('users/{id}/options', [User::class, 'options'])->asForm();
ApiRoute::post('users/{id}/upload-avatar', [User::class, 'uploadAvatar'])->asMultipart();

ApiRoute::resource('users/{user_id}/posts', Post::class)
    ->only('index', 'store', 'destroy');

ApiRoute::resource('categories', Category::class)
    ->only('index', 'store', 'destroy');
