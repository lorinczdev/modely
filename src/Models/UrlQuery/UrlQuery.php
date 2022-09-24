<?php

namespace Lorinczdev\Modely\Models\UrlQuery;

use Stringable;

abstract class UrlQuery implements Stringable
{
    protected array $compiledData = [];

    public function __construct()
    {
    }

    public function __toString(): string
    {
        $queryString = '?';

        if (empty($this->compiledData)) {
            return '';
        }

        return $queryString.http_build_query($this->compiledData);
    }
}
