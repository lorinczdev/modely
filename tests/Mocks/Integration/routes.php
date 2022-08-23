<?php

use Lorinczdev\Modely\Routing\ApiRouter as Route;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\Post;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

Route::post('/{model}/lock', 'lock')->asAction()->partOf('locking');

Route::resource('users', User::class)->addActionGroup('locking');

Route::put('users/{id}/promote', [User::class, 'promote']);

Route::post('users/{id}/options', [User::class, 'options'])->asForm();
Route::post('users/{id}/upload-avatar', [User::class, 'uploadAvatar'])->asMultipart();

Route::resource('users/{user_id}/posts', Post::class)
    ->only('index', 'store', 'destroy');
