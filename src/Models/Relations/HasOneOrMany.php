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
        return static::class === HasMany::class;
    }

    /**
     * Transform provided data to Models.
     */
    public function fill(Model|array|Collection|null $data): Model|Collection|null
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
        return collect($data)->map(fn ($item) => $this->fillOne($item));
    }
}
