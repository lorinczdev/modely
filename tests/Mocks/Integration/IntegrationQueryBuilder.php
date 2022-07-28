<?php

namespace Lorinczdev\Modely\Tests\Mocks\Integration;

use Lorinczdev\Modely\Models\QueryBuilder;

class IntegrationQueryBuilder extends QueryBuilder
{
    public function build(): string
    {
        $queryString = '?';
        $queryData = [];

        foreach ($this->query as $key => [$column, , $value]) {
            $queryData[$column] = $value;
        }

        return $queryString . http_build_query($queryData);
    }
}
