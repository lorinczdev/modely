<?php

namespace Lorinczdev\Modely\Models;

use ArrayAccess;
use BadMethodCallException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Concerns\GuardsAttributes;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use JsonSerializable;
use LogicException;
use Lorinczdev\Modely\Http\ApiClient;
use Lorinczdev\Modely\Http\ApiRequest;
use Lorinczdev\Modely\Models\Concerns\HasParameters;
use Lorinczdev\Modely\Models\Concerns\HasRelationships;
use Lorinczdev\Modely\Models\Concerns\HasTimestamps;

/**
 * @mixin Builder
 *
 * @method static static first()
 */
abstract class Model implements Arrayable, ArrayAccess, Jsonable, JsonSerializable
{
    use HasAttributes;
    use GuardsAttributes;
    use HasRelationships;
    use HasTimestamps;
    use ForwardsCalls;
    use HasEvents;
    use HidesAttributes;
    use HasParameters;

    /**
     * The name of the "created at" column.
     *
     * @var string|null
     */
    protected const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    protected const UPDATED_AT = 'updated_at';

    /**
     * @var array Project configuration.
     */
    protected array $config;

    /**
     * Indicates if the model exists.
     */
    public bool $exists = false;

    /**
     * Indicates if the model was inserted during the current request lifecycle.
     */
    public bool $wasRecentlyCreated = false;

    protected bool $incrementing = true;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
     */
    protected string $keyType = 'int';

    /**
     * Indicates that the object's string representation should be escaped when __toString is invoked.
     */
    protected bool $escapeWhenCastingToString = false;

    protected array $passthru = [
        'get',
        'hydrate',
        'create',
        'insert',
        'find',
        'first',
        'where',
        'skip',
        'offset',
        'limit',
        'forPage',
        'updateOrCreate',
        'whereFirst',
        'whereIn',
        'firstOrCreate',
        'firstOrFail',
        'paginate',
    ];

    final public function __construct(?array $data = [])
    {
        $this->syncOriginal();

        $this->fill($data);
    }

    /**
     * Fill the model with an array of attributes.
     */
    public function fill(array $attributes, bool $sync = false): static
    {
        $totallyGuarded = $this->totallyGuarded();

        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            if ($this->isRelation($key) && $this->isRelationFillable($value)) {
                $value = $this->{$key}()->fill($value);
            }

            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } elseif ($totallyGuarded) {
                throw new MassAssignmentException(sprintf(
                    'Add [%s] to fillable property to allow mass assignment on [%s].',
                    $key, get_class($this)
                ));
            }
        }

        if ($this->getKey()) {
            $this->exists = true;
        }

        if ($sync) {
            $this->syncOriginal();
        }

        return $this;
    }

    /**
     * Get the value of the model's primary key.
     */
    public function getKey(): mixed
    {
        return $this->getAttribute($this->getKeyName());
    }

    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    /**
     * Destroy the models for the given IDs.
     */
    public static function destroy(array|int|string|Collection $ids): int
    {
        if ($ids instanceof Collection) {
            $ids = $ids->all();
        }

        $ids = is_array($ids) ? $ids : func_get_args();

        if (count($ids) === 0) {
            return 0;
        }

        // We will actually pull the models from the database table and call delete on
        // each of them individually so that their events get fired properly with a
        // correct set of attributes in case the developers wants to check these.
        $key = ($instance = new static)->getKeyName();

        if (count($ids) === 1) {
            $result = $instance->find($ids[0])?->delete();

            return (int) $result;
        }

        $count = 0;

        foreach ($instance->whereIn($key, $ids)->get() as $model) {
            if ($model->delete()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get all the models from the API.
     *
     * @return Collection<int, static>
     */
    public static function all(): Collection
    {
        return static::query()->get();
    }

    /**
     * Returns new query instance.
     */
    public static function query(): Builder
    {
        return (new static)->newQuery();
    }

    /**
     * Returns new query instance.
     */
    public function newQuery(): Builder
    {
        return new Builder($this);
    }

    /**
     * Delete the model from the database.
     */
    public function delete()
    {
        $this->mergeAttributesFromCachedCasts();

        if (is_null($this->getKeyName())) {
            throw new LogicException('No primary key defined on model.');
        }

        // If the model doesn't exist, there is nothing to delete so we'll just return
        // immediately and not do anything else. Otherwise, we will continue with a
        // deletion process on the model, firing the proper events, and so forth.
        if (! $this->exists) {
            return;
        }

        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }

        $this->newQuery()->delete();

        $this->setAttribute('deletedAt', now());

        $this->exists = false;

        // Once the model has been deleted, we will fire off the deleted event so that
        // the developers may hook into post-delete operations. We will then return
        // a boolean true as the delete is presumably successful on the database.
        $this->fireModelEvent('deleted', false);

        return true;
    }

    /**
     * Handle dynamic static method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters)
    {
        return (new static())->$method(...$parameters);
    }

    /**
     * Fill the model with an array of attributes. Force mass assignment.
     */
    public function forceFill(array $attributes): static
    {
        return static::unguarded(fn () => $this->fill($attributes));
    }

    /**
     * Create a new model instance that is existing.
     */
    public function newFromBuilder(array $attributes = []): static
    {
        $model = $this->newInstance([], true);

        $model->fill($attributes, true);

        $model->fireModelEvent('retrieved', false);

        return $model;
    }

    /**
     * Create a new instance of the given model.
     */
    public function newInstance(array $attributes = [], bool $exists = false): static
    {
        // This method just provides a convenient way for us to generate fresh model
        // instances of this current model. It is particularly useful during the
        // hydration of new objects via the Eloquent query builder instances.
        $model = new static;

        $model->exists = $exists;

        $model->mergeCasts($this->casts);

        $model->fill($attributes);

        return $model;
    }

    /**
     * Update the model in the database within a transaction.
     */
    public function updateOrFail(array $attributes = []): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->fill($attributes)->saveOrFail();
    }

    /**
     * Update the model in the database without raising any events.
     */
    public function updateQuietly(array $attributes = []): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->fill($attributes)->saveQuietly();
    }

    /**
     * Save the model to the database without raising any events.
     */
    public function saveQuietly(): bool
    {
        return static::withoutEvents(fn () => $this->save());
    }

    /**
     * Save the model to the database.
     */
    public function save(): bool
    {
        $this->mergeAttributesFromCachedCasts();

        $query = $this->newQuery();

        // If the "saving" event returns false we'll bail out of the save and return
        // false, indicating that the save failed. This provides a chance for any
        // listeners to cancel save operations if validations fail or whatever.
        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        if ($this->exists) {
            $saved = ! $this->isDirty() || $this->performUpdate($query);
        } else {
            $saved = $this->performInsert($query);
        }

        // If the model is successfully saved, we need to do a few more things once
        // that is done. We will call the "saved" method here to run any actions
        // we need to happen after a model gets successfully saved right here.
        if ($saved) {
            $this->finishSave();
        }

        return true;
    }

    /**
     * Perform a model update operation.
     */
    protected function performUpdate(Builder $query): bool
    {
        // If the updating event returns false, we will cancel the update operation so
        // developers can hook Validation systems into their models and cancel this
        // operation if the model does not pass validation. Otherwise, we update.
        if ($this->fireModelEvent('updating') === false) {
            return false;
        }

        // Once we have run the update operation, we will fire the "updated" event for
        // this model instance. This will allow developers to hook into these after
        // models are updated, giving them a chance to do any special processing.
        $dirty = $this->getDirty();

        if (count($dirty) > 0) {
            $query->update($dirty);

            $this->syncChanges();

            $this->fireModelEvent('updated', false);
        }

        return true;
    }

    /**
     * Update the model in the database.
     */
    public function update(array $attributes = []): bool
    {
        if (! $this->exists) {
            return false;
        }

        return $this->fill($attributes)->save();
    }

    /**
     * Perform a model insert operation.
     */
    protected function performInsert(Builder $query): bool
    {
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }

        $attributes = $this->getAttributesForInsert();

        if (empty($attributes)) {
            return true;
        }

        $newAttributes = $this->insert($attributes);

        $this->fill($newAttributes, true);

        $this->wasRecentlyCreated = true;

        $this->fireModelEvent('created', false);

        return true;
    }

    /**
     * Perform any actions that are necessary after the model is saved.
     */
    protected function finishSave(): void
    {
        $this->fireModelEvent('saved', false);

        $this->exists = true;

        $this->syncOriginal();
    }

    /**
     * Delete the model from the database without raising any events.
     */
    public function deleteQuietly(): bool
    {
        return static::withoutEvents(fn () => $this->delete());
    }

    /**
     * Delete the model from the database within a transaction.
     */
    public function deleteOrFail(): bool|null
    {
        if (! $this->exists) {
            return false;
        }

        $this->delete();
    }

    public function isDeleted(): bool
    {
        return (bool) $this->deletedAt;
    }

    /**
     * @param  array  $models
     * @return Collection<int, static>
     */
    public function newCollection(array $models = []): Collection
    {
        return new Collection($models);
    }

    public function loadMissing(string $string): void
    {
    }

    /**
     * Get the auto-incrementing key type.
     */
    public function getKeyType(): string
    {
        return $this->keyType;
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     */
    public function getIncrementing(): bool
    {
        return $this->incrementing;
    }

    /**
     * Reload a fresh model instance from the database.
     */
    public function fresh(): static|null
    {
        if (! $this->exists) {
            return null;
        }

        return $this->find($this->getKey());
    }

    /**
     * Reload the current model instance with fresh attributes from the database.
     */
    public function refresh(): static
    {
        if (! $this->exists) {
            return $this;
        }

        $this->setRawAttributes(
            $this->find($this->getKey())->attributes
        );

        $this->syncOriginal();

        return $this;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getClient(): ApiClient
    {
        return new ($this->getConfig()['client']);
    }

    public function getConfig(): array
    {
        return app(static::class)->config;
    }

    public function execute(string $method, array $data = []): \Lorinczdev\Modely\Http\ApiResponse
    {
        return ApiRequest::use($this->newQuery()->getQuery())->send($method, $data);
    }

    public function __call(string $method, array $arguments): mixed
    {
        if (in_array($method, $this->passthru, true)) {
            return $this->forwardCallTo($this->newQuery(), $method, $arguments);
        }

        if ($route = \Lorinczdev\Modely\Facades\ApiRoute::find(static::class, $method)) {
            return $this->execute($method, ...$arguments);
        }

        // exception
        throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', $this::class, $method));
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set(string $key, mixed $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset(string $key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return ! is_null($this->getAttribute($offset));
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset(string $key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset], $this->relations[$offset]);
    }

    /**
     * Convert the model to its string representation.
     */
    public function __toString(): string
    {
        return $this->escapeWhenCastingToString
            ? e($this->toJson())
            : $this->toJson();
    }

    /**
     * Convert the model instance to JSON.
     */
    public function toJson($options = 0): string
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw JsonEncodingException::forModel($this, json_last_error_msg());
        }

        return $json;
    }

    /**
     * Convert the object into something JSON serializable.
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Convert the model instance to an array.
     */
    public function toArray(): array
    {
        return array_merge($this->attributesToArray(), $this->relationsToArray());
    }
}
