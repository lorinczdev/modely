<?php

namespace Lorinczdev\Modely\Models\Relations;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lorinczdev\Modely\Models\Builder;
use Lorinczdev\Modely\Models\Model;

abstract class Relation
{
    /**
     * @param class-string<Model> $relationModelClass
     * @param Model               $parent
     * @param string              $foreignKey
     * @param string              $localKey
     */
    public function __construct(
        public string $relationModelClass,
        public Model  $parent,
        public string $foreignKey,
        public string $localKey
    )
    {
    }

    /**
     * Create new record.
     *
     * @param array|null $data
     * @return Model
     */
    public function create(?array $data = []): Model
    {
        // Initiate relation model
        $model = new $this->relationModelClass($data);

        // For each relation model foreign keys are set
        $this->setForeignKey($model);

        // Store model
        $model->save();

        // // Refresh parent model data
        // $this->parent->refresh();

        return $model;
    }

    /**
     * Set foreign keys.
     */
    protected function setForeignKey(Model $model): Model
    {
        $model->foreignKey = $this->parent->getAttribute($this->localKey);
        $model->foreignKeyName = $this->foreignKey;

        return $model;
    }

    /**
     * Prepare plural version of relation name.
     */
    protected function pluralRelationName(): string
    {
        // E.g. 'company' => 'companies'
        return (string) Str::of($this->relationName())->plural();
    }

    /**
     * Normalize Model class name.
     */
    protected function relationName(): string
    {
        // E.g. Raynet\Models\BusinessCase => 'businessCase'
        return (string) Str::of(class_basename($this->relationModelClass))->lcfirst();
    }

    /**
     * Get first record.
     */
    public function first(): ?Model
    {
        // Run get all records but with limit set to 1
        // Will ask for only one result trough API
        return $this->query()->first();
    }

    /**
     * Return query instance base on relation model class.
     */
    protected function query(?array $query = []): Builder
    {
        // Initiate relation model
        /** @var Model $model */
        $model = new $this->relationModelClass();

        // Set foreign keys
        $this->setForeignKey($model);

        // Return new query instance for relation model
        return $model->newQuery();
    }

    /**
     * Get records.
     *
     * @return Collection<Model>
     */
    public function get(): Collection
    {
        // Get all records
        $models = $this->query()->get();

        // Set foreign keys for each model
        $models = $models->map(fn ($model) => $this->setForeignKey($model));

        return $models;
    }

    /**
     * Transform single model.
     */
    protected function fillOne(array|Model|null $data = null): ?Model
    {
        // Return null when data is null
        if (! $data) {
            return null;
        }

        // Transform data to a model if not done already
        if (! $data instanceof Model) {
            $model = new $this->relationModelClass($data, $this->parent);
        } else {
            $model = $data;
        }

        // Set foreign keys
        $this->setForeignKey($model);

        return $model;
    }

    public function where(string|array $column, mixed $operator = null, mixed $value = null): Builder
    {
        return $this->query()->where(...func_get_args());
    }
}
