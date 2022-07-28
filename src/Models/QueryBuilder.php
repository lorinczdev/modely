<?php

namespace Lorinczdev\Modely\Models;

abstract class QueryBuilder
{
    public function __construct(
        protected array $query
    )
    {
    }

    abstract public function build(): string;
}
