<?php

use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

$user = new User(['name' => 'Marek']);

$user->save();
// $user->saveQuietly();

$user->update(['name' => 'Keram']);
// $user->updateOrFail(['name' => 'Keram']);
// $user->updateQuietly(['name' => 'Keram']);

// User::findOrNew(1);
// User::firstOrNew(['name' => 'Marek']);
User::firstOrCreate(['name' => 'Marek']);
User::updateOrCreate(['name' => 'Marek']);

User::create(['name' => 'Marek']);
// User::forceCreate(['name' => 'Marek']);
