<?php

namespace Lorinczdev\Modely\Models\Concerns;

use Lorinczdev\Modely\Models\Model;
use Lorinczdev\Modely\Models\Relations\HasMany;
use Lorinczdev\Modely\Models\Relations\HasOne;
use Str;

trait HasRelationships
{
    public ?string $foreignKeyName = null;

    public ?int $foreignKey = null;

    /**
     * The loaded relationships for the model.
     */
    protected array $relations = [];

    public function getForeignKeyName(): string
    {
        return Str::of(class_basename($this::class))->lcfirst();
    }

    /**
     * Determine if the given relation is loaded.
     */
    public function relationLoaded(string $key): bool
    {
        return array_key_exists($key, $this->relations);
    }

    /**
     * Check if model has registered provided relationship.
     *
     * @param string $relationship
     * @return bool
     */
    protected function hasRelationship(string $relationship): bool
    {
        return method_exists($this, $relationship);
    }

    /**
     * Create has many relationship.
     *
     * @param class-string<Model> $className
     * @return HasMany
     */
    protected function hasMany(string $className): HasMany
    {
        $foreignKeyName = $this->getForeignKeyName();
        $foreignKey = $this->getKey();

        return new HasMany($className, $this, $foreignKeyName, $foreignKey);
    }

    /**
     * Create has one relationship.
     *
     * @param class-string $className
     * @return HasOne
     */
    protected function hasOne(string $className): HasOne
    {
        $foreignKeyName = $this->getForeignKeyName();
        $foreignKey = $this->getKey();

        return new HasOne($className, $this, $foreignKeyName, $foreignKey);
    }
}
