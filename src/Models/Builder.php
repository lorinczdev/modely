<?php

namespace Lorinczdev\Modely\Models;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use Lorinczdev\Modely\Models\Pagination\Pagination;

/**
 * @mixin Query
 * @template T of Model
 */
class Builder
{
    use ForwardsCalls;

    protected Query $query;

    /**
     * The methods that should be returned from query builder.
     *
     * @var string[]
     */
    protected array $passthru = [
        // 'aggregate',
        // 'average',
        // 'avg',
        // 'count',
        'doesntExist',
        'exists',
        'insert',
        // 'max',
        // 'min',
        // 'sum',
    ];

    /**
     * @param  T  $model
     */
    public function __construct(
        protected Model $model,
    ) {
        $this->query = new Query($model);
    }

    /**
     * Add a where clause on the primary key to the query.
     */
    public function whereKeyNot(Model|string|int|array $id): static
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        if (is_array($id) || $id instanceof Arrayable) {
            $this->query->whereNotIn($this->model->getKeyName(), $id);

            return $this;
        }

        if ($id !== null && $this->model->getKeyType() === 'string') {
            $id = (string) $id;
        }

        return $this->where($this->model->getKeyName(), '!=', $id);
    }

    public function whereFirst(string|array $column, mixed $operator = null, mixed $value = null, $boolean = 'and'): ?Model
    {
        return $this->where(...func_get_args())->first();
    }

    public function first()
    {
        return $this->take(1)->get()->first();
    }

    /**
     * Execute the query as a "select" statement.
     */
    public function get(): Collection
    {
        $builder = $this->applyScopes();

        $models = $builder->getModels();

        return $builder->getModel()->newCollection($models);
    }

    /**
     * Apply the scopes to the Eloquent builder instance and return it.
     */
    public function applyScopes(): static
    {
        return $this;
        //     if (! $this->scopes) {
        //         return $this;
        //     }
        //
        //     $builder = clone $this;
        //
        //     foreach ($this->scopes as $identifier => $scope) {
        //         if (! isset($builder->scopes[$identifier])) {
        //             continue;
        //         }
        //
        //         $builder->callScope(function (self $builder) use ($scope) {
        //             // If the scope is a Closure we will just go ahead and call the scope with the
        //             // builder instance. The "callScope" method will properly group the clauses
        //             // that are added to this query so "where" clauses maintain proper logic.
        //             if ($scope instanceof Closure) {
        //                 $scope($builder);
        //             }
        //
        //             // If the scope is a scope object, we will call the apply method on this scope
        //             // passing in the builder and the model instance. After we run all of these
        //             // scopes we will return back the builder instance to the outside caller.
        //             if ($scope instanceof Scope) {
        //                 $scope->apply($builder, $this->getModel());
        //             }
        //         });
        //     }
        //
        //     return $builder;
    }

    /**
     * Get the hydrated models.
     *
     * @return Model[]
     */
    public function getModels(): array
    {
        return $this->model->hydrate(
            $this->query->get()->all()
        )->all();
    }

    /**
     * Create a collection of models from plain arrays.
     */
    public function hydrate(array $items): Collection
    {
        $instance = $this->newModelInstance();

        return $instance->newCollection(
            array_map(fn ($item) => $instance->newFromBuilder($item), $items)
        );
    }

    /**
     * Create a new instance of the model being queried.
     */
    public function newModelInstance(array $attributes = []): Model
    {
        return $this->model->newInstance($attributes);
    }

    /**
     * Get the model instance being queried.
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Add an "or where" clause to the query.
     */
    public function orWhere(string|array|Closure $column, mixed $operator = null, mixed $value = null): static
    {
        [$value, $operator] = $this->query->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Add an "or where not" clause to the query.
     */
    public function orWhereNot(string|array|Closure $column, mixed $operator = null, mixed $value = null): static
    {
        return $this->whereNot($column, $operator, $value, 'or');
    }

    /**
     * Add a basic "where not" clause to the query.
     */
    public function whereNot(string|array|Closure $column, mixed $operator = null, mixed $value = null, $boolean = 'and'): static
    {
        return $this->where($column, $operator, $value, $boolean.' not');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     */
    public function latest(?string $column = null): static
    {
        if (is_null($column)) {
            $column = $this->model->getCreatedAtColumn() ?? 'created_at';
        }

        $this->query->latest($column);

        return $this;
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     */
    public function oldest(?string $column = null): static
    {
        if (is_null($column)) {
            $column = $this->model->getCreatedAtColumn() ?? 'created_at';
        }

        $this->query->oldest($column);

        return $this;
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @return Collection|T
     */
    public function findOrFail(mixed $id): Collection|Model
    {
        $result = $this->find($id);

        $id = $id instanceof Arrayable ? $id->toArray() : $id;

        if (is_array($id)) {
            if (count($result) !== count(array_unique($id))) {
                throw (new ModelNotFoundException)->setModel(
                    get_class($this->model), array_diff($id, $result->pluck($this->model->getKeyName()))
                );
            }

            return $result;
        }

        if (is_null($result)) {
            throw (new ModelNotFoundException)->setModel(
                get_class($this->model), $id
            );
        }

        return $result;
    }

    /**
     * @return Collection|null|T
     */
    public function find(mixed $id): null|Model|Collection
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return $this->findMany($id);
        }

        $item = $this->query->find($id);

        if (! $item) {
            return null;
        }

        return $this->hydrate([$item])->first();
    }

    /**
     * Find multiple models by their primary keys.
     */
    public function findMany(Arrayable|array $ids): Collection
    {
        $ids = $ids instanceof Arrayable ? $ids->toArray() : $ids;

        if (empty($ids)) {
            return $this->model->newCollection();
        }

        return $this->whereKey($ids)->get();
    }

    /**
     * Add a where clause on the primary key to the query.
     */
    public function whereKey(Model|string|int|array $id): static
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }

        if (is_array($id) || $id instanceof Arrayable) {
            $this->query->whereIn($this->model->getKeyName(), $id);

            return $this;
        }

        if ($id !== null && $this->model->getKeyType() === 'string') {
            $id = (string) $id;
        }

        return $this->where($this->model->getKeyName(), '=', $id);
    }

    /**
     * Add a basic where clause to the query.
     */
    public function where(string|array|Closure $column,
                          mixed $operator = null,
                          mixed $value = null,
                          string $boolean = 'and'): self
    {
        // if ($column instanceof Closure && is_null($operator)) {
        //     $column($query = $this->model->newQuery());
        //
        //     $this->query->addNestedWhereQuery($query->getQuery(), $boolean);
        // } else {
        $this->query->where(...func_get_args());
        // }

        return $this;
    }

    /**
     * Find a model by its primary key or return fresh model instance.
     *
     * @return T
     */
    public function findOrNew(mixed $id): Model
    {
        if (! is_null($model = $this->find($id))) {
            return $model;
        }

        return $this->newModelInstance();
    }

    /**
     * Get the first record matching the attributes or create it.
     *
     * @return T
     */
    public function firstOrCreate(array $attributes = [], array $values = []): Model
    {
        if (! is_null($instance = $this->where($attributes)->first())) {
            return $instance;
        }

        return tap($this->newModelInstance(array_merge($attributes, $values)), function ($instance) {
            $instance->save();
        });
    }

    /**
     * Create or update a record matching the attributes, and fill it with values.
     *
     * @return T
     */
    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return tap($this->firstOrNew($attributes), function ($instance) use ($values) {
            $instance->fill($values)->save();
        });
    }

    /**
     * Get the first record matching the attributes or instantiate it.
     *
     * @return T
     */
    public function firstOrNew(array $attributes = [], array $values = []): Model
    {
        if (! is_null($instance = $this->where($attributes)->first())) {
            return $instance;
        }

        return $this->newModelInstance(array_merge($attributes, $values));
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @return T
     */
    public function firstOrFail(): Model
    {
        if (! is_null($model = $this->first())) {
            return $model;
        }

        throw (new ModelNotFoundException)->setModel(get_class($this->model));
    }

    /**
     * Save a new model and return the instance. Allow mass-assignment.
     *
     * @return T
     */
    public function forceCreate(array $attributes): Model
    {
        return $this->model->unguarded(fn () => $this->newModelInstance()->create($attributes));
    }

    /**
     * Save a new model and return the instance.
     *
     * @return T
     */
    public function create(array $attributes = []): Model
    {
        return tap($this->newModelInstance($attributes), function ($instance) {
            $instance->save();
        });
    }

    /**
     * Update records in the database.
     */
    public function update(array $values): array
    {
        return $this->toBase()->update($values);
    }

    /**
     * Get a base query builder instance.
     */
    public function toBase(): Query
    {
        return $this->applyScopes()->getQuery();
    }

    /**
     * Get the underlying query builder instance.
     */
    public function getQuery(): Query
    {
        return $this->query;
    }

    /**
     * Determine if the given model has a scope.
     */
    // public function hasNamedScope(string $scope): bool
    // {
    //     return $this->model && $this->model->hasNamedScope($scope);
    // }

    /**
     * Call the given local model scopes.
     *
     * @param  array|string  $scopes
     * @return static|mixed
     */
    // public function scopes($scopes)
    // {
    //     $builder = $this;
    //
    //     foreach (Arr::wrap($scopes) as $scope => $parameters) {
    //         // If the scope key is an integer, then the scope was passed as the value and
    //         // the parameter list is empty, so we will format the scope name and these
    //         // parameters here. Then, we'll be ready to call the scope on the model.
    //         if (is_int($scope)) {
    //             [$scope, $parameters] = [$parameters, []];
    //         }
    //
    //         // Next we'll pass the scope callback to the callScope method which will take
    //         // care of grouping the "wheres" properly so the logical order doesn't get
    //         // messed up when adding scopes. Then we'll return back out the builder.
    //         $builder = $builder->callNamedScope(
    //             $scope, Arr::wrap($parameters)
    //         );
    //     }
    //
    //     return $builder;
    // }

    /**
     * Set the underlying query builder instance.
     */
    public function setQuery(Query $query): static
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Apply the given scope on the current builder instance.
     *
     * @param  callable  $scope
     * @param  array  $parameters
     * @return mixed
     */
    // protected function callScope(callable $scope, array $parameters = [])
    // {
    //     array_unshift($parameters, $this);
    //
    //     $query = $this->getQuery();
    //
    //     // We will keep track of how many wheres are on the query before running the
    //     // scope so that we can properly group the added scope constraints in the
    //     // query as their own isolated nested where statement and avoid issues.
    //     $originalWhereCount = is_null($query->wheres)
    //                 ? 0 : count($query->wheres);
    //
    //     $result = $scope(...$parameters) ?? $this;
    //
    //     if (count((array) $query->wheres) > $originalWhereCount) {
    //         $this->addNewWheresWithinGroup($query, $originalWhereCount);
    //     }
    //
    //     return $result;
    // }

    /**
     * Apply the given named scope on the current builder instance.
     */
    // protected function callNamedScope(string $scope, array $parameters = []): mixed
    // {
    //     return $this->callScope(function (...$parameters) use ($scope) {
    //         return $this->model->callNamedScope($scope, $parameters);
    //     }, $parameters);
    // }

    /**
     * Run the default delete function on the builder.
     *
     * Since we do not apply scopes here, the row will actually be deleted.
     */
    public function forceDelete(): mixed
    {
        return $this->query->delete();
    }

    /**
     * Delete records from the database.
     */
    public function delete(): mixed
    {
        if (isset($this->onDelete)) {
            return call_user_func($this->onDelete, $this);
        }

        return $this->toBase()->delete();
    }

    /**
     * Dynamically handle calls into the query instance.
     */
    public function __call(string $method, array $parameters): mixed
    {
        // if ($this->hasNamedScope($method)) {
        //     return $this->callNamedScope($method, $parameters);
        // }

        if (in_array($method, $this->passthru)) {
            return $this->toBase()->{$method}(...$parameters);
        }

        $this->forwardCallTo($this->query, $method, $parameters);

        return $this;
    }

    public function paginate(int $perPage = null, int $page = 1, string $method = 'index'): Pagination
    {
        $perPage = $perPage ?: $this->model->getConfig()['query']['perPage'] ?? 15;

        return new Pagination($this, $perPage, $page, $method);
    }
}
