<?php

namespace Lorinczdev\Modely\Models\Relations;

use Lorinczdev\Modely\Models\Builder;
use Lorinczdev\Modely\Models\Model;

abstract class MorphOneOrMany extends HasOneOrMany
{
    /**
     * The foreign key type for the relationship.
     */
    protected string $morphType;

    /**
     * The class name of the parent model.
     */
    protected string $morphClass;

    /**
     * Create a new morph one or many relationship instance.
     */
    public function __construct(string $related, Model $parent, string $type, string $id, string $localKey)
    {
        $this->morphType = $type;

        $this->morphClass = $parent->getMorphClass();

        parent::__construct($related, $parent, $id, $localKey);
    }


    /**
     * Get the plain morph type name without the table.
     */
    public function getMorphType(): string
    {
        return $this->morphType;
    }

    /**
     * Get the class name of the parent model.
     */
    public function getMorphClass(): string
    {
        return $this->morphClass;
    }
}