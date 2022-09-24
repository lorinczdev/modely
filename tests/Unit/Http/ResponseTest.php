<?php

use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\RequestException;

it('can get data', function () {
    $response = new Lorinczdev\Modely\Http\ApiResponse(
        new \Illuminate\Http\Client\Response(
            response: new Psr7Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['id' => 1])
            )
        )
    );

    expect($response->data())->toBe(['id' => 1]);
});

it('can check if content type is json', function () {
    $response = new Lorinczdev\Modely\Http\ApiResponse(
        new \Illuminate\Http\Client\Response(
            response: new Psr7Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode(['id' => 1])
            )
        )
    );

    expect(
        $response->isJson()
    )->toBe(true);
});

it('logs response', function () {
    new Lorinczdev\Modely\Http\ApiResponse(
        new \Illuminate\Http\Client\Response(
            new Psr7Response(
                500,
                ['Content-Type' => 'application/json'],
                json_encode(['id' => 1])
            )
        )
    );
})->throws(RequestException::class);
