<?php

it('can get data', function () {
    $response = new Lorinczdev\Modely\Http\Response(['id' => 1]);

    expect($response->getData())->toBe(['id' => 1]);
});
