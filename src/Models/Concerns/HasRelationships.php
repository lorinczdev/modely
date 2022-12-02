<?php

namespace Lorinczdev\Modely\Models\Concerns;

use Illuminate\Support\Collection;
use Lorinczdev\Modely\Models\Model;
use Lorinczdev\Modely\Models\Relations\BelongsTo;
use Lorinczdev\Modely\Models\Relations\HasMany;
use Lorinczdev\Modely\Models\Relations\HasOne;
use Lorinczdev\Modely\Models\Relations\MorphMany;
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
        return in_array(gettype($value), [Model::class, 'array', Collection::class, 'NULL']);
    }

    /**
     * Set the given relationship on the model.
     */
    public function setRelation(string $relation, mixed $value, bool $sync = false): static
    {
        $this->relations[$relation] = $this->{$relation}()->fill($value, $sync);

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

    /**
     * Set the entire relations array on the model.
     */
    public function setRelations(array $relations): static
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * Duplicate the instance and unset all the loaded relations.
     *
     * @return $this
     */
    public function withoutRelations(): static
    {
        return (clone $this)->unsetRelations();
    }

    /**
     * Unset all the loaded relations for the instance.
     *
     * @return $this
     */
    public function unsetRelations(): static
    {
        $this->relations = [];

        return $this;
    }

    /**
     * Get a specified relationship.
     */
    public function getRelation(string $relation): mixed
    {
        return $this->relations[$relation];
    }

    public function getForeignKey(): string
    {
        return Str::snake(class_basename($this)).'_'.$this->getKeyName();
    }

    /**
     * Get all the loaded relations for the instance.
     */
    public function getRelations(): array
    {
        return $this->relations;
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

    /**
     * Define a polymorphic one-to-many relationship.
     */
    public function morphMany(string $related, string $name, ?string $type = null, ?string $id = null, ?string $localKey = null): MorphMany
    {
        // $instance = $this->newRelatedInstance($related);

        // Here we will gather up the morph type and ID for the relationship so that we
        // can properly query the intermediate table of a relation. Finally, we will
        // get the table and create the relationship instances for the developers.
        [$type, $id] = $this->getMorphs($name, $type, $id);

        $localKey = $localKey ?: $this->getKeyName();

        return $this->newMorphMany($related, $this, $type, $id, $localKey);
    }

    /**
     * Instantiate a new MorphMany relationship.
     */
    protected function newMorphMany(string $related, Model $parent, string $type, string $id, string $localKey): MorphMany
    {
        return new MorphMany($related, $parent, $type, $id, $localKey);
    }

    /**
     * Get the polymorphic relationship columns.
     */
    protected function getMorphs(string $name, ?string $type, ?string $id): array
    {
        return [$type ?: $name.'_type', $id ?: $name.'_id'];
    }

    /**
     * Get the class name for polymorphic relations.
     */
    public function getMorphClass(): string
    {
        // $morphMap = Relation::morphMap();
        //
        // if (! empty($morphMap) && in_array(static::class, $morphMap)) {
        //     return array_search(static::class, $morphMap, true);
        // }
        //
        // if (static::class === Pivot::class) {
        //     return static::class;
        // }
        //
        // if (Relation::requiresMorphMap()) {
        //     throw new ClassMorphViolationException($this);
        // }

        return static::class;
    }

    /**
     * Create a new model instance for a related model.
     */
    protected function newRelatedInstance(string $class): Model
    {
        return new $class;
    }

    /**
     * Get the dynamic relation resolver if defined or inherited, or return null.
     *
     * @param  string  $class
     * @param  string  $key
     * @return mixed
     */
    public function relationResolver($class, $key)
    {
        if ($resolver = static::$relationResolvers[$class][$key] ?? null) {
            return $resolver;
        }

        if ($parent = get_parent_class($class)) {
            return $this->relationResolver($parent, $key);
        }

        return null;
    }
}
