<?php

namespace Lorinczdev\Modely\Tests\Mocks\Integration;

use Lorinczdev\Modely\Models\Query;
use Lorinczdev\Modely\Models\UrlQuery\UrlQuery;
use RuntimeException;

class IntegrationUrlQuery extends UrlQuery
{
    public function compileQuery(Query $query): string
    {
        $this->compiledData = $this->compile($query);

        // dd($this->compiledData);

        return $this;
    }

    public function compileWhere(
        ?string $type = null,
        ?string $column = null,
        ?string $operator = null,
        mixed $value = null,
        mixed $values = null,
        ?string $boolean = null,
        ?Query $query = null,
    ): array {
        if ($type === 'In') {
            $set = [];

            foreach ($values as $key => $v) {
                $set[$column][$key] = $v;
            }

            return $set;
        }

        if ($type === 'Nested') {
            return $this->compile($query);
        }

        if ($type === 'Basic') {
            return [$column => $value];
        }

        throw new RuntimeException('Unhandled type: '.$type);
    }

    public function compile(Query $query): array
    {
        $compiledData = [];

        foreach ($query->wheres as $value) {
            $compiledData = [
                ...$compiledData,
                ...$this->compileWhere(...$value),
            ];
        }

        if ($query->limit) {
            $compiledData['limit'] = $query->limit;
        }

        if ($query->offset) {
            $compiledData['offset'] = $query->offset;
        }

        if ($query->orders) {
            foreach ($query->orders as $key => $order) {
                if (! isset($compiledData['sortColumn'])) {
                    $compiledData['sortColumn'] = [];
                    $compiledData['sortDirection'] = [];
                }

                $compiledData['sortColumn'][$key] = $order['column'];
                $compiledData['sortDirection'][$key] = $order['direction'];
            }
        }

        return $compiledData;
    }
}
