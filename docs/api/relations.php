<?php

use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

$user = User::first();

// Should work the same way as model

// read
$user->posts()->get();
$user->posts()->where('title', 'Post A')->first();

// save
$user->posts()->create(['title' => 'Post A']);
$user->posts()->first()->save();
$user->posts()->first()->update(['title' => 'Post B']);

// destory
$user->posts()->delete();

// TODO in future make it work more like Laravel's relationships
// ->with()
// ->load()
// ->loadMssing()
// etc.
