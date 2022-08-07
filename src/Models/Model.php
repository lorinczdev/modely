<?php

namespace Lorinczdev\Modely\Models;

use ArrayAccess;
use BadMethodCallException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\ForwardsCalls;
use Lorinczdev\Modely\Http\Request;
use Lorinczdev\Modely\Models\Concerns\HasQuery;
use Lorinczdev\Modely\Models\Concerns\HasRelationships;
use Lorinczdev\Modely\Modely;

abstract class Model implements Arrayable, ArrayAccess
{
    use HasQuery;
    use HasAttributes;
    use HasRelationships;
    use HasTimestamps;
    use ForwardsCalls;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
     */
    protected string $keyType = 'int';

    public bool $exists = false;

    /**
     * // TODO do we need this?
     * Indicates if the IDs are auto-incrementing.
     */
    public bool $incrementing = false;

    /**
     * TODO what to do with these
     * The name of the "created at" column.
     *
     * @var string|null
     */
    protected const CREATED_AT = 'created_at';

    /**
     * TODO what to do with these
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    protected const UPDATED_AT = 'updated_at';

    final public function __construct(?array $data = [])
    {
        $this->fill($data);

        // if ($parent) {
        //     $this->setParentId($parent);
        // }
    }

    /**
     * Fill the model with an array of attributes.
     */
    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if ($this->isRelation($key)) {
                $value = $this->{$key}()->fill($value);
            }

            $this->setAttribute($key, $value);
        }

        if ($this->getKey()) {
            $this->exists = true;
        }

        return $this;
    }

    public function getClient()
    {
        $config = Modely::getConfig(static::class);

        return new $config['client'];
    }

    public function getConfig(): array
    {
        return Modely::getConfig(static::class);
    }

    public function save(): bool
    {
        if ($this->exists) {
            Request::for($this)->send('update', $this->getAttributes());
        } else {
            $response = Request::for($this)->send('store', $this->getAttributes());

            $this->fill($response->getData());
        }

        $this->exists = true;

        return true;
    }

    public function update(array $attributes = []): bool
    {
        if (!$this->exists) {
            return false;
        }

        return $this->fill($attributes)->save();
    }

    public function delete(): void
    {
        Request::for($this)->send('destroy');

        $this->setAttribute('deletedAt', now());

        $this->exists = false;
    }

    public function isDeleted(): bool
    {
        return !!$this->deletedAt;
    }

    /**
     * @param array $items
     * @return Collection<int, static>
     */
    public function newCollection(array $items): Collection
    {
        return (new Collection($items))->map(fn($item) => new static($item));
    }

    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    /**
     * Get the value of the model's primary key.
     */
    public function getKey(): mixed
    {
        return $this->getAttribute($this->getKeyName());
    }

    // TODO
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
     * Convert the model instance to an array.
     */
    public function toArray(): array
    {
        return array_merge($this->attributesToArray(), $this->relationsToArray());
    }

    public function __call(string $method, array $arguments): mixed
    {
        if (in_array($method, $this->queryMethods, true)) {
            return $this->forwardCallTo($this->newQuery(), $method, $arguments);
        }

        if ($route = app(Route::class)->find(static::class, $method)) {
            return Request::for($this)->send($method);
        }

        // exception
        throw new BadMethodCallException(sprintf('Call to undefined method %s::%s()', $this::class, $method));
    }

    /**
     * Handle dynamic static method calls into the model.
     *
     * @param string $method
     * @param array  $parameters
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters)
    {
        return (new static())->$method(...$parameters);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public function __set(string $key, mixed $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return !is_null($this->getAttribute($offset));
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset], $this->relations[$offset]);
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param string $key
     * @return void
     */
    public function __unset(string $key)
    {
        $this->offsetUnset($key);
    }
}
