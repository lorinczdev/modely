<?php

namespace Lorinczdev\Modely\Models\Relations;

use Illuminate\Support\Collection;
use Lorinczdev\Modely\Models\Model;

abstract class HasOneOrMany extends Relation
{
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
        if (! $this->isHasMany()) {
            $this->parent->{$this->relationName()}->delete();
        }
    }

    /**
     * Check if current relation is has many.
     */
    protected function isHasMany(): bool
    {
        return static::class === HasMany::class || static::class === MorphMany::class;
    }

    /**
     * Transform provided data to Models.
     */
    public function fill(Model|array|Collection|null $data, bool $sync = false): Model|Collection|null
    {
        // Transform many models
        if ($this->isHasMany()) {
            return $this->fillMany($data, $sync);
        }

        // Transform single model
        return $this->fillOne($data, $sync);
    }

    /**
     * Transform multiple models.
     */
    protected function fillMany(array|Collection|null $data = [], bool $sync = false): Collection
    {
        // Return if data are already collection
        if ($data instanceof Collection) {
            return $data;
        }

        // Return collection with items transformed to models
        return collect($data)->map(fn ($item) => $this->fillOne($item, $sync));
    }
}
