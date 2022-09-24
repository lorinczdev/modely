<?php

namespace Lorinczdev\Modely\Models\Relations;

use Lorinczdev\Modely\Models\Model;

class BelongsTo extends Relation
{
    /**
     * Delete record.
     */
    public function delete(): void
    {
        $this->parent->{$this->relationName()}->delete();
    }

    /**
     * Transform provided data to Models.
     */
    public function fill(Model|array|null $data): ?Model
    {
        // Transform single model
        return $this->fillOne($data);
    }
}
