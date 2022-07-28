<?php

namespace Lorinczdev\Modely\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Lorinczdev\Modely\Http\Request;
use Lorinczdev\Modely\Models\Pagination\Pagination;
use Str;

class Query
{
    protected Request $request;

    public function __construct(
        protected Model|string|null $modelClass = null,
        public ?array               $query = []
    )
    {
        $this->request = new Request(query: $this);
    }

    public function get(): Collection
    {
        $response = $this->request->send('index');

        return $this->getModel()->newCollection($response->getData());
    }

    public function getModel(): Model
    {
        return is_string($this->modelClass) ? new $this->modelClass() : $this->modelClass;
    }

    public function create(array $data): Model
    {
        $model = $this->getModel();

        $model->fill($data)->save();

        return $model;
    }

    public function find($id): ?Model
    {
        // if (is_array($id) || $id instanceof Arrayable) {
        //     return $this->findMany($id, $columns);
        // }

        $response = Request::for(query: $this)
            ->withParameters([$this->getModel()->getKeyName() => $id])
            ->send('show');

        $data = $response->getData();

        if (!$data) {
            return null;
        }

        return $this->getModel()->fill($data);
    }

    public function first()
    {
        return $this->take(1)->get()->first();
    }

    /**
     * Alias to set the "limit" value of the query.
     */
    public function take(int $value): static
    {
        return $this->limit($value);
    }

    public function limit(int $number): self
    {
        $this->addQuery('limit', $number);

        return $this;
    }

    /**
     * Add a where clause on the primary key to the query.
     */
    public function whereKey(Model|string|int $id): static
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        return $this->where($this->getModel()->getKeyName(), $id);
    }

    // public function setModel(Model|string $modelClass): self
    // {
    //     $this->modelClass = $modelClass;
    //
    //     return $this;
    // }

    // public function setQuery(array $query): self
    // {
    //     $this->query = $query;
    //
    //     return $this;
    // }

    public function firstOrCreate(array $queryData, ?array $storeData = []): Model
    {
        $model = $this
            ->where(
                array_map(function ($value, $key) {
                    return [$key, $value];
                }, $queryData, array_keys($queryData))
            )
            ->first();

        if (!$model) {
            $model = $this->getModel();
            $model->fill([
                ...$queryData,
                ...$storeData
            ]);
            $model->save();
        }

        return $model;
    }

    public function createOrUpdate(array $queryData, ?array $updateData = []): Model
    {
        $model = $this
            ->where(
                array_map(function ($value, $key) {
                    return [$key, $value];
                }, $queryData, array_keys($queryData))
            )
            ->first();

        $model = $model ?: $this->getModel();

        $model->fill([
            ...$queryData,
            ...$updateData
        ]);

        $model->save();

        return $model;
    }

    public function where(string|array $column, mixed $operator = null, mixed $value = null): self
    {
        if (is_array($column)) {
            foreach ($column as $arguments) {
                $this->where(...$arguments);
            }

            return $this;
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '==';
        }

        $this->addQuery($column, $operator, $value);

        return $this;
    }

    public function addQuery(string $column, mixed $operator, mixed $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '';
        }

        Arr::set($this->query, $column, [$column, $operator, $value]);

        return $this;
    }

    public function whereFirst(string|array $column, mixed $operator = null, mixed $value = null): ?Model
    {
        return $this->where(...func_get_args())->first();
    }

    public function skip(int $number): self
    {
        $this->addQuery('offset', $number);

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->addQuery('sortColumn', $column);
        $this->addQuery('sortDirection', Str::upper($direction));

        return $this;
    }

    public function paginate(int $perPage = 15, int $page = 1): Pagination
    {
        return new Pagination($this, $perPage, $page);
    }

    public function page(int $page): static
    {
        $this->addQuery('page', $page);

        return $this;
    }

    public function builder(): QueryBuilder
    {
        $builder = $this->getModel()->getConfig()['query']['builder'] ?? ArrayQueryBuilder::class;

        return new $builder($this->query);
    }

    public function getQueryValue(string $key): mixed
    {
        return Arr::last($this->query[$key] ?? []);
    }
}
