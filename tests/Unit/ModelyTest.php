<?php

use Lorinczdev\Modely\Modely;

it('it registers provided integration', function () {
    $modely = app(DummyModely::class);

    $modely->register('integration', __DIR__ . '/../Mocks/Integration');

    expect($modely->integrations)->toHaveKey('integration')
        ->and($modely->getIntegration('integration'))->toBeArray();
});

class DummyModely extends Modely
{
    public array $integrations = [];
}
