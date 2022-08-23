<?php

use Lorinczdev\Modely\Facades\ApiRoute;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

ApiRoute::get('users', [User::class, 'index']);
ApiRoute::post('users', [User::class, 'update']);
ApiRoute::put('users', [User::class, 'update']);
ApiRoute::patch('users', [User::class, 'update']);
ApiRoute::delete('users', [User::class, 'destroy']);

ApiRoute::post('users/{id}/options', [User::class, 'form'])->asForm();
ApiRoute::post('users/{id}/upload-avatar', [User::class, 'uploadAvatar'])->asMultipart();

ApiRoute::resource('users', User::class);
ApiRoute::resource('users', User::class)->only('index');
ApiRoute::resource('users', User::class)->except('destroy');

ApiRoute::resource('users', User::class)->addActionGroup('locking');
ApiRoute::resource('users', User::class)->addAction('lock');

ApiRoute::post('/{id}/lock', 'lock')->asAction()->partOf('locking');

ApiRoute::group(['prefix' => 'users'], function () {
    ApiRoute::get('/', [User::class, 'index']);
});

// Route::resource('{foreign_key_name}/{foreign_key}/address', Address::class)
//     ->except('get', 'show');
// Route::post('{foreign_key_name}/{foreign_key}/address/{id}/setPrimary', [Address::class, 'setPrimary']);
// Route::post('{foreign_key_name}/{foreign_key}/address/{id}/setContact', [Address::class, 'setContact']);
