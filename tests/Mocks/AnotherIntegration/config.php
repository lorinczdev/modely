<?php

use Lorinczdev\Modely\Tests\Mocks\Integration\ApiClient;
use Lorinczdev\Modely\Tests\Mocks\Integration\IntegrationUrlQuery;

return [
    'name' => 'another-integration',

    'routes' => __DIR__ . '/routes.php',

    'dir' => [
        'models' => __DIR__ . '/Models',
    ]
];
