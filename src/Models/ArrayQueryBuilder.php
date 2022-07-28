<?php

namespace Lorinczdev\Modely\Models;

class ArrayQueryBuilder extends QueryBuilder
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
