<?php

use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

$user = new User(['id' => 1]);

$user->delete();

User::destroy(1);
User::destroy(1, 2, 3);
User::destroy([1, 2, 3]);
