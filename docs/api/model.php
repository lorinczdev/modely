<?php

// custom actions

use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

$user = new User();

// execute 'promote' action
$user->promote();

// execute 'options' action that'll send data application/multi-part
$user->uploadAvatar([
    'name' => 'file',
    'contents' => '',
    'filename' => null,
    'headers' => []
]);

// execute 'options' action that'll send data application/x-www-form-urlencoded
$user->options([
    'role' => 'cat'
]);

// custom actions
$user->execute('update', ['test']);
