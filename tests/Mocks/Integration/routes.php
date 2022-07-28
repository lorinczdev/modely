<?php

use Lorinczdev\Modely\Routing\Route;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Post;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

Route::resource('users', User::class);

Route::resource('users/{foreign_key}/posts', Post::class)
    ->only('index', 'store', 'destroy');
