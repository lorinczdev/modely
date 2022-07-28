<?php

namespace Lorinczdev\Modely\Models\Pagination;

use Illuminate\Support\Collection;
use Lorinczdev\Modely\Models\Query;

class Pagination extends Collection
{
    protected int $pages;

    protected int $total;

    protected PaginateCollection $collection;

    protected bool $emptyResponse = false;

    public function __construct(
        protected Query $query,
        protected int   $perPage = 15,
        protected int   $page = 1
    )
    {
        parent::__construct();

        $this->query->page($this->page);
        $this->query->limit($this->perPage);

        $this->collection = new PaginateCollection($this->fetch());
    }

    public function next(): static
    {
        ++$this->page;

        $this->query->page($this->page);

        $this->collection = new PaginateCollection($this->fetch());

        return $this;
    }

    public function previous(): static
    {
        if ($this->page === 1) {
            return $this;
        }

        --$this->page;

        $this->query->page($this->page);

        $this->collection = new PaginateCollection($this->fetch());

        return $this;
    }

    protected function fetch(): array
    {
        return $this->getArrayableItems(
            $this->query->get()
        );
    }

    public function isLastPage(): bool
    {
        return $this->collection->count() < $this->perPage;
    }

    public function more(): static
    {
        ++$this->page;

        $this->query->page($this->page);

        $this->collection = $this->collection->merge($data = $this->fetch());

        if (empty($data)) {
            $this->emptyResponse = true;
        }

        return $this;
    }

    public function fetchAll(int $limitPages = 0): static
    {
        while ($this->collection->count() % $this->perPage === 0 && !$this->emptyResponse) {
            $this->more();

            if ($limitPages !== 0 && $this->page >= $limitPages) {
                break;
            }
        }

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

    public function untilLast(callable $callback): void
    {
        while (true) {
            $continue = true;

            $this->getCollection()->each(function ($item) use (&$continue, $callback) {
                $continue = $callback($item);
            });

            if ($continue === false) {
                break;
            }

            if ($this->isLastPage()) {
                break;
            }

            $this->next();
        }
    }

    public function toArray(): array
    {
        return $this->collection->all();
    }
}
