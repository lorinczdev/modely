<?php

use Lorinczdev\Modely\Tests\Mocks\Integration\ApiClient;
use Lorinczdev\Modely\Tests\Mocks\Integration\IntegrationUrlQuery;

return [
    'name' => 'integration',

    'client' => ApiClient::class,

    'query' => [
        'compiler' => IntegrationUrlQuery::class,
    ],

    'request' => \Lorinczdev\Modely\Tests\Mocks\Integration\ApiRequest::class,

    'response' => \Lorinczdev\Modely\Tests\Mocks\Integration\ApiResponse::class,

    'routes' => __DIR__.'/routes.php',

    'dir' => [
        'models' => __DIR__.'/Models',
    ],
];
