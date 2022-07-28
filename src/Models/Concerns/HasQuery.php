<?php

namespace Lorinczdev\Modely\Models\Concerns;

use Illuminate\Support\Collection;
use Lorinczdev\Modely\Models\Model;
use Lorinczdev\Modely\Models\Query;

/**
 * @method static Collection<int, Model> get()
 * @method static static create(array $data)
 * @method static static find(mixed $id)
 // * @method static Model first()
 // * @method static Query where(string|array $column, mixed $operator = null, mixed $value = null)
 // * @method static Model|null whereFirst(string|array $column, mixed $operator = null, mixed $value = null)
 // * @method static Model createOrUpdate(array $query, array $data)
 // * @method static Model firstOrCreate(array $query, array $data)
 // * @method static Pagination paginate(int $perPage = 20, int $page = 1, string $endpoint = 'index')
 */
trait HasQuery
{
    protected array $queryMethods = [
        'get',
        'create',
        'find',
        // 'first',
        // 'where',
        // 'skip',
        // 'limit',
        // 'page',
        // 'createOrUpdate',
        // 'whereFirst',
        // 'firstOrCreate',
        // 'paginate'
    ];

    /**
     * Returns new query instance.
     *
     * @param array<string, mixed>|null $query
     */
    public function newQuery(?array $query = []): Query
    {
        return new Query($this, $query);
    }

    public static function query(): Query
    {
        return (new static)->newQuery();
    }
}
