<?php

namespace Lorinczdev\Modely\Models;

use BadMethodCallException;
use Carbon\CarbonPeriod;
use Closure;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use InvalidArgumentException;
use Lorinczdev\Modely\Http\ApiRequest;

class Query
{
    use ForwardsCalls;

    /**
     * An aggregate function and column to be run.
     */
    public array $aggregate;

    /**
     * The columns that should be returned.
     */
    public array $columns;

    /**
     * The where constraints for the query.
     */
    public array $wheres = [];

    /**
     * The groupings for the query.
     */
    public array $groups;

    /**
     * The orderings for the query.
     */
    public ?array $orders = null;

    /**
     * The maximum number of records to return.
     */
    public ?int $limit = null;

    /**
     * The number of records to skip.
     */
    public ?int $offset = null;

    /**
     * All of the available clause operators.
     */
    public array $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '&', '|', '^', '<<', '>>', '&~', 'is', 'is not',
        'rlike', 'not rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
    ];

    protected ApiRequest $request;

    public static bool $ignoreMissingOperatos = false;

    public function __construct(protected Model $model)
    {
        $this->request = $this->resolveRequest();
    }

    public function resolveRequest(): ApiRequest
    {
        $requestClass = $this->model->getConfig()['request'] ?? ApiRequest::class;

        return new $requestClass(query: $this);
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Execute a query for a single record by ID.
     */
    public function find(int $id): ?array
    {
        $response = $this->request
            ->withParameters([$this->model->getKeyName() => $id])
            ->send('show');

        return $response->data();
    }

    /**
     * Alias to set the "offset" value of the query.
     */
    public function skip(int $value): self
    {
        return $this->offset($value);
    }

    /**
     * Set the "offset" value of the query.
     */
    public function offset(int $value): self
    {
        $this->offset = max(0, (int) $value);

        return $this;
    }

    /**
     * Alias to set the "limit" value of the query.
     */
    public function take(int $value): self
    {
        return $this->limit($value);
    }

    /**
     * Set the "limit" value of the query.
     */
    public function limit(int $value): self
    {
        if ($value >= 0) {
            $this->limit = ! is_null($value) ? (int) $value : null;
        }

        return $this;
    }

    /**
     * Set the columns to be selected.
     */
    public function select(array|string $columns): self
    {
        $this->columns = [];

        $columns = is_array($columns) ? $columns : func_get_args();

        foreach ($columns as $column) {
            $this->columns[] = $column;
        }

        return $this;
    }

    /**
     * Add a new select column to the query.
     */
    public function addSelect(array|string $column): self
    {
        $columns = is_array($column) ? $column : func_get_args();

        foreach ($columns as $column) {
            $this->columns[] = $column;
        }

        return $this;
    }

    /**
     * Add an "or where" clause to the query.
     */
    public function orWhere(
        string|array|Closure $column,
        mixed $operator = null,
        mixed $value = null
    ): self {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Prepare the value and operator for a where clause.
     *
     * @throws InvalidArgumentException
     */
    public function prepareValueAndOperator(?string $value, string $operator, bool $useDefault = false): array
    {
        if ($useDefault) {
            return [$operator, '='];
        }

        if ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
    }

    /**
     * Determine if the given operator and value combination is legal.
     *
     * Prevents using Null values with invalid operators.
     */
    protected function invalidOperatorAndValue(string $operator, mixed $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (self::$ignoreMissingOperatos) {
            return false;
        }

        return in_array($operator, $this->operators) &&
            ! in_array($operator, ['=', '<>', '!=']);
    }

    /**
     * Add a basic where clause to the query.
     */
    public function where(
        string|array|Closure $column,
        mixed $operator = null,
        mixed $value = null,
        string $boolean = 'and'
    ): self {
        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }

        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        // If the column is actually a Closure instance, we will assume the developer
        // wants to begin a nested where statement which is wrapped in parentheses.
        // We will add that Closure to the query and return back out immediately.
        // if ($column instanceof Closure && is_null($operator)) {
        //     return $this->whereNested($column, $boolean);
        // }

        // If the column is a Closure instance and there is an operator value, we will
        // assume the developer wants to run a subquery and then compare the result
        // of that subquery with the given value that was provided to the method.
        // if ($this->isQueryable($column) && ! is_null($operator)) {
        //     [$sub, $bindings] = $this->createSub($column);
        //
        //     return $this->addBinding($bindings, 'where')
        //         ->where(new Expression('('.$sub.')'), $operator, $value, $boolean);
        // }

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        // If the value is a Closure, it means the developer is performing an entire
        // sub-select within the query and we will need to compile the sub-select
        // within the where clause to get the appropriate query record results.
        // if ($value instanceof Closure) {
        //     return $this->whereSub($column, $operator, $value, $boolean);
        // }

        // If the value is "null", we will just assume the developer wants to add a
        // where null clause to the query. So, we will allow a short-cut here to
        // that method for convenience so the developer doesn't have to check.
        if (is_null($value)) {
            return $this->whereNull($column, $boolean, $operator !== '=');
        }

        $type = 'Basic';

        // If the column is making a JSON reference we'll check to see if the value
        // is a boolean. If it is, we'll add the raw boolean string as an actual
        // value to the query to ensure this is properly handled by the query.
        // if (str_contains($column, '->') && is_bool($value)) {
        //     $value = new Expression($value ? 'true' : 'false');
        //
        //     if (is_string($column)) {
        //         $type = 'JsonBoolean';
        //     }
        // }

        // if ($this->isBitwiseOperator($operator)) {
        //     $type = 'Bitwise';
        // }

        // Now that we are working with just a simple query we can put the elements
        // in our array and add the query binding to our array of bindings that
        // will be bound to each SQL statements when it is finally executed.
        $this->wheres[] = compact(
            'type', 'column', 'operator', 'value', 'boolean'
        );

        return $this;
    }

    /**
     * Add an array of where clauses to the query.
     */
    protected function addArrayOfWheres(array $column, string $boolean, string $method = 'where'): self
    {
        return $this->whereNested(function ($query) use ($column, $method, $boolean) {
            foreach ($column as $key => $value) {
                if (is_numeric($key) && is_array($value)) {
                    $query->{$method}(...array_values($value));
                } else {
                    $query->$method($key, '=', $value, $boolean);
                }
            }
        }, $boolean);
    }

    /**
     * Add a nested where statement to the query.
     */
    public function whereNested(Closure $callback, string $boolean = 'and'): self
    {
        $callback($query = $this->forNestedWhere());

        return $this->addNestedWhereQuery($query, $boolean);
    }

    /**
     * Create a new query instance for nested where condition.
     */
    public function forNestedWhere(): self
    {
        return $this->newQuery();
    }

    /**
     * Get a new instance of the query builder.
     */
    public function newQuery(): Query
    {
        return new static($this->model);
    }

    /**
     * Add another query builder as a nested where to the query builder.
     */
    public function addNestedWhereQuery(Query $query, string $boolean = 'and'): self
    {
        if (count($query->wheres)) {
            $type = 'Nested';

            $this->wheres[] = compact('type', 'query', 'boolean');
        }

        return $this;
    }

    /**
     * Determine if the given operator is supported.
     */
    protected function invalidOperator(mixed $operator): bool
    {
        return ! is_string($operator) || (! in_array(strtolower($operator), $this->operators, true));
    }

    /**
     * Add a "where null" clause to the query.
     */
    public function whereNull(array|string $columns, string $boolean = 'and', bool $not = false): self
    {
        $type = $not ? 'NotNull' : 'Null';

        foreach (Arr::wrap($columns) as $column) {
            $this->wheres[] = compact('type', 'column', 'boolean');
        }

        return $this;
    }

    /**
     * Add an "or where not" clause to the query.
     *
     * @param  Closure|string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return $this
     */
    public function orWhereNot(
        string|array|Closure $column,
        mixed $operator = null,
        mixed $value = null
    ): self {
        return $this->whereNot($column, $operator, $value, 'or');
    }

    /**
     * Add a basic "where not" clause to the query.
     */
    public function whereNot(
        string|array|Closure $column,
        mixed $operator = null,
        mixed $value = null,
        string $boolean = 'and'
    ): self {
        return $this->where($column, $operator, $value, $boolean.' not');
    }

    /**
     * Add an "or where" clause comparing two columns to the query.
     */
    public function orWhereColumn(array|string $first, string $operator = null, string $second = null): self
    {
        return $this->whereColumn($first, $operator, $second, 'or');
    }

    /**
     * Add a "where" clause comparing two columns to the query.
     */
    public function whereColumn(array|string $first, string $operator = null, string $second = null, ?string $boolean = 'and'): self
    {
        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (is_array($first)) {
            return $this->addArrayOfWheres($first, $boolean, 'whereColumn');
        }

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$second, $operator] = [$operator, '='];
        }

        // Finally, we will add this where clause into this array of clauses that we
        // are building for the query. All of them will be compiled via a grammar
        // once the query is about to be executed and run against the database.
        $type = 'Column';

        $this->wheres[] = compact(
            'type', 'first', 'operator', 'second', 'boolean'
        );

        return $this;
    }

    /**
     * Add an "or where in" clause to the query.
     */
    public function orWhereIn(string $column, mixed $values): self
    {
        return $this->whereIn($column, $values, 'or');
    }

    /**
     * Add a "where in" clause to the query.
     */
    public function whereIn(string $column, mixed $values, string $boolean = 'and', bool $not = false): self
    {
        $type = $not ? 'NotIn' : 'In';

        // Next, if the value is Arrayable we need to cast it to its raw array form so we
        // have the underlying array value instead of an Arrayable object which is not
        // able to be added as a binding, etc. We will then add to the wheres array.
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');

        return $this;
    }

    /**
     * Add an "or where not in" clause to the query.
     */
    public function orWhereNotIn(string $column, mixed $values): self
    {
        return $this->whereNotIn($column, $values, 'or');
    }

    /**
     * Add a "where not in" clause to the query.
     */
    public function whereNotIn(string $column, mixed $values, string $boolean = 'and'): self
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add an "or where null" clause to the query.
     */
    public function orWhereNull(array|string $column): self
    {
        return $this->whereNull($column, 'or');
    }

    /**
     * Add an or where between statement to the query.
     */
    public function orWhereBetween(string $column, iterable $values): self
    {
        return $this->whereBetween($column, $values, 'or');
    }

    /**
     * Add a where between statement to the query.
     */
    public function whereBetween(string $column, iterable $values, string $boolean = 'and', bool $not = false): self
    {
        $type = 'Between';

        if ($values instanceof CarbonPeriod) {
            $values = $values->toArray();
        }

        $this->wheres[] = compact('type', 'column', 'values', 'boolean', 'not');

        return $this;
    }

    /**
     * Add an or where between statement using columns to the query.
     */
    public function orWhereBetweenColumns(string $column, array $values): self
    {
        return $this->whereBetweenColumns($column, $values, 'or');
    }

    /**
     * Add a where between statement using columns to the query.
     */
    public function whereBetweenColumns(string $column, array $values, string $boolean = 'and', bool $not = false): self
    {
        $type = 'betweenColumns';

        $this->wheres[] = compact('type', 'column', 'values', 'boolean', 'not');

        return $this;
    }

    /**
     * Add an or where not between statement to the query.
     */
    public function orWhereNotBetween(string $column, iterable $values): self
    {
        return $this->whereNotBetween($column, $values, 'or');
    }

    /**
     * Add a where not between statement to the query.
     */
    public function whereNotBetween(string $column, iterable $values, string $boolean = 'and'): self
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    /**
     * Add an or where not between statement using columns to the query.
     */
    public function orWhereNotBetweenColumns(string $column, array $values): self
    {
        return $this->whereNotBetweenColumns($column, $values, 'or');
    }

    /**
     * Add a where not between statement using columns to the query.
     */
    public function whereNotBetweenColumns(string $column, array $values, string $boolean = 'and'): self
    {
        return $this->whereBetweenColumns($column, $values, $boolean, true);
    }

    /**
     * Add an "or where not null" clause to the query.
     */
    public function orWhereNotNull(string $column): self
    {
        return $this->whereNotNull($column, 'or');
    }

    /**
     * Add a "where not null" clause to the query.
     */
    public function whereNotNull(array|string $columns, string $boolean = 'and'): self
    {
        return $this->whereNull($columns, $boolean, true);
    }

    /**
     * Add an "or where date" statement to the query.
     */
    public function orWhereDate(string $column, string $operator, DateTimeInterface|string $value = null): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereDate($column, $operator, $value, 'or');
    }

    /**
     * Add a "where date" statement to the query.
     */
    public function whereDate(string $column, string $operator, DateTimeInterface|string $value = null, string $boolean = 'and'): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }

        return $this->addDateBasedWhere('Date', $column, $operator, $value, $boolean);
    }

    /**
     * Get a scalar type value from an unknown type of input.
     */
    protected function flattenValue(mixed $value): mixed
    {
        return is_array($value) ? head(Arr::flatten($value)) : $value;
    }

    /**
     * Add a date based (year, month, day, time) statement to the query.
     */
    protected function addDateBasedWhere(string $type, string $column, string $operator, mixed $value, string $boolean = 'and'): self
    {
        $this->wheres[] = compact('column', 'type', 'boolean', 'operator', 'value');

        return $this;
    }

    /**
     * Add an "or where time" statement to the query.
     */
    public function orWhereTime(string $column, string $operator, DateTimeInterface|string $value = null): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereTime($column, $operator, $value, 'or');
    }

    /**
     * Add a "where time" statement to the query.
     */
    public function whereTime(string $column, string $operator, DateTimeInterface|string $value = null, string $boolean = 'and'): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('H:i:s');
        }

        return $this->addDateBasedWhere('Time', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where day" statement to the query.
     */
    public function orWhereDay(string $column, string $operator, DateTimeInterface|string $value = null): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereDay($column, $operator, $value, 'or');
    }

    /**
     * Add a "where day" statement to the query.
     */
    public function whereDay(string $column, string $operator, DateTimeInterface|string $value = null, string $boolean = 'and'): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('d');
        }

        return $this->addDateBasedWhere('Day', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where month" statement to the query.
     */
    public function orWhereMonth(string $column, string $operator, DateTimeInterface|string $value = null): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereMonth($column, $operator, $value, 'or');
    }

    /**
     * Add a "where month" statement to the query.
     */
    public function whereMonth(string $column, string $operator, DateTimeInterface|string $value = null, string $boolean = 'and'): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('m');
        }

        return $this->addDateBasedWhere('Month', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where year" statement to the query.
     */
    public function orWhereYear(string $column, string $operator, DateTimeInterface|int|string $value = null): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereYear($column, $operator, $value, 'or');
    }

    /**
     * Add a "where year" statement to the query.
     */
    public function whereYear(string $column, string $operator, DateTimeInterface|int|string $value = null, string $boolean = 'and'): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y');
        }

        return $this->addDateBasedWhere('Year', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where JSON contains" clause to the query.
     */
    public function orWhereJsonContains(string $column, mixed $value): self
    {
        return $this->whereJsonContains($column, $value, 'or');
    }

    /**
     * Add a "where JSON contains" clause to the query.
     */
    public function whereJsonContains(string $column, mixed $value, string $boolean = 'and', bool $not = false): self
    {
        $type = 'JsonContains';

        $this->wheres[] = compact('type', 'column', 'value', 'boolean', 'not');

        return $this;
    }

    /**
     * Add an "or where JSON not contains" clause to the query.
     */
    public function orWhereJsonDoesntContain(string $column, mixed $value): self
    {
        return $this->whereJsonDoesntContain($column, $value, 'or');
    }

    /**
     * Add a "where JSON not contains" clause to the query.
     */
    public function whereJsonDoesntContain(string $column, mixed $value, string $boolean = 'and'): self
    {
        return $this->whereJsonContains($column, $value, $boolean, true);
    }

    /**
     * Add an "or" clause that determines if a JSON path exists to the query.
     */
    public function orWhereJsonContainsKey(string $column): self
    {
        return $this->whereJsonContainsKey($column, 'or');
    }

    /**
     * Add a clause that determines if a JSON path exists to the query.
     */
    public function whereJsonContainsKey(string $column, string $boolean = 'and', bool $not = false): self
    {
        $type = 'JsonContainsKey';

        $this->wheres[] = compact('type', 'column', 'boolean', 'not');

        return $this;
    }

    /**
     * Add an "or" clause that determines if a JSON path does not exist to the query.
     */
    public function orWhereJsonDoesntContainKey(string $column): self
    {
        return $this->whereJsonDoesntContainKey($column, 'or');
    }

    /**
     * Add a clause that determines if a JSON path does not exist to the query.
     */
    public function whereJsonDoesntContainKey(string $column, string $boolean = 'and'): self
    {
        return $this->whereJsonContainsKey($column, $boolean, true);
    }

    /**
     * Add an "or where JSON length" clause to the query.
     */
    public function orWhereJsonLength(string $column, mixed $operator, mixed $value = null): self
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereJsonLength($column, $operator, $value, 'or');
    }

    /**
     * Add a "where JSON length" clause to the query.
     */
    public function whereJsonLength(string $column, mixed $operator, mixed $value = null, string $boolean = 'and'): self
    {
        $type = 'JsonLength';

        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');

        return $this;
    }

    /**
     * Add a "or where fulltext" clause to the query.
     */
    public function orWhereFullText(array|string $columns, string $value, array $options = []): self
    {
        return $this->whereFulltext($columns, $value, $options, 'or');
    }

    /**
     * Add a "where fulltext" clause to the query.
     */
    public function whereFullText(array|string $columns, string $value, array $options = [], string $boolean = 'and'): self
    {
        $type = 'Fulltext';

        $columns = (array) $columns;

        $this->wheres[] = compact('type', 'columns', 'value', 'options', 'boolean');

        return $this;
    }

    /**
     * Add a "group by" clause to the query.
     *
     * @param  array|string  ...$groups
     * @return $this
     */
    public function groupBy(...$groups): self
    {
        foreach ($groups as $group) {
            $this->groups = array_merge(
                (array) $this->groups,
                Arr::wrap($group)
            );
        }

        return $this;
    }

    /**
     * Add a descending "order by" clause to the query.
     */
    public function orderByDesc(string $column): self
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add an "order by" clause to the query.
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $direction = strtolower($direction);

        if (! in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Order direction must be "asc" or "desc".');
        }

        $this->orders[] = [
            'column' => $column,
            'direction' => $direction,
        ];

        return $this;
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     */
    public function latest(string $column = 'created_at'): self
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     */
    public function oldest(string $column = 'created_at'): self
    {
        return $this->orderBy($column, 'asc');
    }

    /**
     * Set the limit and offset for a given page.
     */
    public function forPage(int $page, int $perPage = 15): self
    {
        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }

    /**
     * Execute the given callback if no rows exist for the current query.
     */
    public function existsOr(Closure $callback): mixed
    {
        return $this->exists() ? true : $callback();
    }

    /**
     * Determine if any rows exist for the current query.
     */
    public function exists(): bool
    {
        $results = $this->get();

        return count($results) > 0;
    }

    /**
     * Execute the query as a "select" statement.
     */
    public function get(): Collection
    {
        $response = $this->request->send('index');

        return collect($response->data());
    }

    /**
     * Execute the given callback if rows exist for the current query.
     */
    public function doesntExistOr(Closure $callback): mixed
    {
        return $this->doesntExist() ? true : $callback();
    }

    /**
     * Determine if no rows exist for the current query.
     */
    public function doesntExist(): bool
    {
        return ! $this->exists();
    }

    /**
     * Insert new records into the database.
     */
    public function insert(array $values): array
    {
        return $this->request->send('store', $values)->data();
    }

    /**
     * Update records in the database.
     */
    public function update(array $values): array
    {
        return $this->request->send('update', $values)->data();
    }

    /**
     * Delete records from the database.
     */
    public function delete(mixed $id = null): void
    {
        $this->request->send('destroy');
    }

    /**
     * Handle dynamic method calls into the method.
     *
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $parameters): mixed
    {
        if (str_starts_with($method, 'where')) {
            return $this->dynamicWhere($method, $parameters);
        }

        static::throwBadMethodCallException($method);
    }

    /**
     * Handles dynamic "where" clauses to the query.
     */
    public function dynamicWhere(string $method, array $parameters): self
    {
        $finder = substr($method, 5);

        $segments = preg_split(
            '/(And|Or)(?=[A-Z])/', $finder, -1, PREG_SPLIT_DELIM_CAPTURE
        );

        // The connector variable will determine which connector will be used for the
        // query condition. We will change it as we come across new boolean values
        // in the dynamic method strings, which could contain a number of these.
        $connector = 'and';

        $index = 0;

        foreach ($segments as $segment) {
            // If the segment is not a boolean connector, we can assume it is a column's name
            // and we will add it to the query as a new constraint as a where clause, then
            // we can keep iterating through the dynamic method string's segments again.
            if ($segment !== 'And' && $segment !== 'Or') {
                $this->addDynamic($segment, $connector, $parameters, $index);

                $index++;
            }

            // Otherwise, we will store the connector so we know how the next where clause we
            // find in the query should be connected to the previous ones, meaning we will
            // have the proper boolean connector to connect the next where clause found.
            else {
                $connector = $segment;
            }
        }

        return $this;
    }

    /**
     * Add a single dynamic where clause statement to the query.
     */
    protected function addDynamic(string $segment, string $connector, array $parameters, int $index): void
    {
        // Once we have parsed out the columns and formatted the boolean operators we
        // are ready to add it to this query as a where clause just like any other
        // clause on the query. Then we'll increment the parameter index values.
        $bool = strtolower($connector);

        $this->where(Str::snake($segment), '=', $parameters[$index], $bool);
    }
}
