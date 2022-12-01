<?php

namespace Lorinczdev\Modely\Models\Pagination;

use Illuminate\Support\Collection;
use Lorinczdev\Modely\Models\Builder;

class Pagination
{
    protected int $pages;

    protected int $total;

    protected PaginateCollection $collection;

    protected bool $emptyResponse = false;

    public function __construct(
        protected Builder $query,
        protected int $perPage = 15,
        protected int $page = 1,
        protected string $method = 'index'
    ) {
        $this->query->forPage($this->page, $this->perPage);

        $this->collection = new PaginateCollection($this->fetch());
    }

    protected function fetch(): array
    {
        $items = $this->query->get();

        $this->emptyResponse = $items->isEmpty();

        return collect($items)->all();
    }

    public function previous(): static
    {
        if ($this->page === 1) {
            return $this;
        }

        $this->query->forPage(--$this->page, $this->perPage);

        $this->collection = new PaginateCollection($this->fetch());

        return $this;
    }

    public function getAll(int $limitPages = 0): PaginateCollection
    {
        return $this->fetchAll($limitPages)->getCollection();
    }

    public function getCollection(): PaginateCollection
    {
        return $this->collection;
    }

    /**
     * @param  int  $limitPages 0 - all pages
     * @return Pagination
     */
    public function fetchAll(int $limitPages = 0): static
    {
        while ($this->collection->count() % $this->perPage === 0 && ! $this->emptyResponse) {
            $this->more();

            if ($limitPages !== 0 && $this->page >= $limitPages) {
                break;
            }
        }

        return $this;
    }

    public function more(): static
    {
        $this->query->forPage(++$this->page, $this->perPage);

        $this->collection = $this->collection->merge($data = $this->fetch());

        if (empty($data)) {
            $this->emptyResponse = true;
        }

        return $this;
    }

    public function untilLast(callable $callback): void
    {
        $continue = true;

        while ($continue) {
            $this->getCollection()->each(function ($item) use (&$continue, $callback) {
                $continue = $callback($item);
            });

            if ($this->isLastPage()) {
                break;
            }

            $this->next();
        }
    }

    public function isLastPage(): bool
    {
        return $this->collection->count() < $this->perPage;
    }

    public function next(): static
    {
        $this->query->forPage(++$this->page, $this->perPage);

        $this->collection = new PaginateCollection($this->fetch());

        return $this;
    }

    public function toArray(): array
    {
        return $this->collection->all();
    }
}
