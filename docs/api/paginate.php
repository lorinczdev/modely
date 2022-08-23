<?php

use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

$pagination = User::paginate(15, 1, 'index');

$pagination->next();
$pagination->previous();

$pagination->isLastPage();
$pagination->getCollection();

// Loads the items from the next page and adds it to the collection.
$pagination->more();

// Fetches all the items from the endpoint.
$pagination->fetchAll();

// Fetches all the items from the endpoint and returns collection.
$pagination->getAll();

$pagination->untilLast(fn (User $user) => $user->delete());
