<?php

use Lorinczdev\Modely\Tests\Mocks\Integration\Client;
use Lorinczdev\Modely\Tests\Mocks\Integration\IntegrationQueryBuilder;

return [
    'name' => 'integration',

    'client' => Client::class,

    'query' => [
        'builder' => IntegrationQueryBuilder::class
    ],

    'routes' => __DIR__ . '/routes.php',
];
