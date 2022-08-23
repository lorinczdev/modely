<?php

use Lorinczdev\Modely\Models\UrlQuery\CompileAsArrayQuery;

beforeEach(function () {
    $this->config = require __DIR__ . '/../../src/default-config.php';
});

it('is stored in a array format', function () {
    expect($this->config)->toBeArray();
});

it('defines query compiler class', function () {
    expect($this->config['query']['compiler'])->toBe(CompileAsArrayQuery::class);
});

it('defines request class', function () {
    expect($this->config['request'])->toBe(\Lorinczdev\Modely\Http\ApiRequest::class);
});

it('defines response class', function () {
    expect($this->config['response'])->toBe(\Lorinczdev\Modely\Http\ApiResponse::class);
});

it('has path to routes file', function () {
    expect($this->config['routes'])->toEndWith('routes.php');
});

it('has path to directory where models are stored', function () {
    expect($this->config['dir']['models'])->toEndWith('/Models');
});
