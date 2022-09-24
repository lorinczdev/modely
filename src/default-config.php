<?php

use Lorinczdev\Modely\Http\ApiClient;
use Lorinczdev\Modely\Models\UrlQuery\CompileAsArrayQuery;

return [
    // Name of the integration it's going to be registerd at.
    'name' => 'modely',

    // Client the integration is going to use for sending http requests.
    'client' => ApiClient::class,

    'query' => [
        // Compiler used to compile query.
        'compiler' => CompileAsArrayQuery::class,
    ],

    // Request used by integration to prepare HTTP request.
    'request' => \Lorinczdev\Modely\Http\ApiRequest::class,

    // Response used by integration to parse HTTP response.
    'response' => \Lorinczdev\Modely\Http\ApiResponse::class,

    // Path to routes file where API endpoints are registered.
    'routes' => __DIR__.'/routes.php',

    'dir' => [
        // Directory where models are stored.
        'models' => __DIR__.'/Models',
    ],
];
