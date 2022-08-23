<?php

use GuzzleHttp\Psr7\Response as Psr7Response;

$response = new \Lorinczdev\Modely\Http\ApiResponse(
    new  \Illuminate\Http\Client\Response(
        response: new Psr7Response(500, ['Content-Type' => 'application/json'], null)
    )
);

// Get data from response
$response->data();
$response->toArray();

// And for registering custom Responses
// /Users/UpdatePostResponse
