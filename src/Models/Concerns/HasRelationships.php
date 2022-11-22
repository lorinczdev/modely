<?php

namespace Lorinczdev\Modely\Models\Concerns;

use Illuminate\Support\Collection;
use Lorinczdev\Modely\Models\Model;
use Lorinczdev\Modely\Models\Relations\BelongsTo;
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

    /**
     * Determine if the given relation is loaded.
     */
    public function relationLoaded(string $key): bool
    {
        return array_key_exists($key, $this->relations);
    }

    public function isRelationFillable(mixed $value): bool
    {
        return in_array(gettype($value), [Model::class, 'array', Collection::class, 'null']);
    }

    /**
     * Set the given relationship on the model.
     */
    public function setRelation(string $relation, mixed $value): static
    {
        // $this->relations[$relation] = $value;
        $this->relations[$relation] = null; // use null keys for now

        return $this;
    }

    /**
     * Unset a loaded relationship.
     */
    public function unsetRelation(string $relation): static
    {
        unset($this->relations[$relation]);

        return $this;
    }
    
    /**
     * Check if model has registered provided relationship.
     *
     * @param  string  $relationship
     * @return bool
     */
    protected function hasRelationship(string $relationship): bool
    {
        return method_exists($this, $relationship);
    }

    public function getForeignKey(): string
    {
        return Str::snake(class_basename($this)).'_'.$this->getKeyName();
    }

    /**
     * Define a one-to-many relationship.
     *
     * @param  class-string<static>  $className
     * @param  string|null  $foreignKey
     * @param  string|null  $localKey
     * @return HasMany
     */
    protected function hasMany(string $className, ?string $foreignKey = null, ?string $localKey = null): HasMany
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return new HasMany($className, $this, $foreignKey, $localKey);
    }

    /**
     * Define a one-to-one relationship.
     *
     * @param  class-string<static>  $className
     * @param  string|null  $foreignKey
     * @param  string|null  $localKey
     * @return HasOne
     */
    protected function hasOne(string $className, ?string $foreignKey = null, ?string $localKey = null): HasOne
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return new HasOne($className, $this, $foreignKey, $localKey);
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @param  class-string<static>  $className
     * @param  string|null  $foreignKey
     * @param  string|null  $localKey
     * @return BelongsTo
     */
    protected function belongsTo(string $className, ?string $foreignKey = null, ?string $localKey = null): BelongsTo
    {
        $instance = new $className();

        $foreignKey = $foreignKey ?: $instance->getForeignKey();

        $localKey = $localKey ?: $this->getKeyName();

        return new BelongsTo($className, $this, $foreignKey, $localKey);
    }
}
