<?php

use Lorinczdev\Modely\Http\Request;
use Lorinczdev\Modely\Models\Model;
use Lorinczdev\Modely\Routing\UnknownRouteException;
use Lorinczdev\Modely\Tests\Mocks\Integration\Models\User;

it('can be initialized statically', function () {
    expect(
        DummyRequest::for(new User)
    )
        ->toBeInstanceOf(Request::class);
});

it('can send a request', function () {
    \Illuminate\Support\Facades\Http::fake([
        '*/users' => \Illuminate\Support\Facades\Http::response(fixture('Users/index')),
    ]);

    $response = DummyRequest::for(model: new User)->send('index');

    expect($response)
        ->toBeInstanceOf(\Lorinczdev\Modely\Http\Response::class);
});

it('throws exception when route was not registered', function () {
    DummyRequest::for(model: new User)->send('terminate');
})->throws(UnknownRouteException::class);

it('allows to pass extra parameters', function () {
    $request = DummyRequest::for(model: new User)->withParameters(['hello' => 'world']);

    expect($request->parameters)
        ->toBe(['hello' => 'world']);
});

it('prepares url', function () {
    $request = DummyRequest::for(model: new User(['id' => 1]))
        ->withParameters(['color' => 'yellow']);

    $route = [
        'url' => 'users/{id}/{color}',
    ];

    expect($request->prepareUrl($route))
        ->toBe('users/1/yellow');
});

it('can get model', function () {
    expect(DummyRequest::for(query: User::query()))
        ->getModel()
        ->toBeInstanceOf(User::class);
});

it('prepares http query string', function () {

    expect(
        DummyRequest::for(
            query: User::query()->where('name', 'Marek')
        )
    )
        ->buildQuery()
        ->toBe('?name=Marek');
});

class DummyRequest extends Lorinczdev\Modely\Http\Request
{
    public array $parameters = [];

    public function prepareUrl(array $route): string
    {
        return parent::prepareUrl($route);
    }

    public function buildQuery(): string
    {
        return parent::buildQuery();
    }

    public function getModel(): Model
    {
        return parent::getModel();
    }
}
