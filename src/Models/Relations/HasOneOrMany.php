<?php

namespace Lorinczdev\Modely\Models\Relations;

use Illuminate\Support\Collection;
use Lorinczdev\Modely\Models\Model;
use Lorinczdev\Modely\Models\Query;
use Str;

abstract class HasOneOrMany
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
     * Delete record.
     */
    public function delete(): void
    {
        // Delete every related model
        if ($this->isHasMany()) {
            $relationName = $this->pluralRelationName();

            // TODO same for bellow
            $this->parent->loadMissing($relationName);

            $this->parent->{$relationName}->each->delete();
        }

        // Delete single related model
        if (!$this->isHasMany()) {
            $this->parent->{$this->relationName()}->delete();
        }
    }

    /**
     * Check if current relation is has many.
     */
    protected function isHasMany(): bool
    {
        return static::class === HasMany::class;
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
     * Get records.
     *
     * @return Collection<Model>
     */
    public function get(): Collection
    {
        // Get all records
        $models = $this->query()->get();

        // Set foreign keys for each model
        $models = $models->map(fn($model) => $this->setForeignKey($model));

        return $models;
    }

    /**
     * Return query instance base on relation model class.
     */
    protected function query(?array $query = []): Query
    {
        // Initiate relation model
        /** @var Model $model */
        $model = new $this->relationModelClass();

        // Set foreign keys
        $this->setForeignKey($model);

        // Return new query instance for relation model
        return $model->newQuery(query: $query);
    }

    /**
     * Transform provided data to Models.
     */
    public function fill(Model|array|Collection|null $data): Model|Collection
    {
        // Transform many models
        if ($this->isHasMany()) {
            return $this->fillMany($data);
        }

        // Transform single model
        return $this->fillOne($data);
    }

    /**
     * Transform multiple models.
     */
    protected function fillMany(array|Collection|null $data = []): Collection
    {
        // Return if data are already collection
        if ($data instanceof Collection) {
            return $data;
        }

        // Return collection with items transformed to models
        return collect($data)->map(fn($item) => $this->fillOne($item));
    }

    /**
     * Transform single model.
     */
    protected function fillOne(array|Model|null $data = null): ?Model
    {
        // Return null when data is null
        if (!$data) {
            return null;
        }

        // Transform data to a model if not done already
        if (!$data instanceof Model) {
            $model = new $this->relationModelClass($data, $this->parent);
        } else {
            $model = $data;
        }

        // Set foreign keys
        $this->setForeignKey($model);

        return $model;
    }

    public function where(string|array $column, mixed $operator = null, mixed $value = null): Query
    {
        return $this->query()->where(...func_get_args());
    }
}