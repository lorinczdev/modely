<?php

namespace Lorinczdev\Modely\Models\Concerns;

use Illuminate\Support\Collection;
use Lorinczdev\Modely\Models\Model;
use Lorinczdev\Modely\Models\Relations\BelongsTo;
use Lorinczdev\Modely\Models\Relations\HasMany;
use Lorinczdev\Modely\Models\Relations\HasOne;
use Str;

trait HasParameters
{
    protected array $parameters = [];

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public static function withParameters(array $parameters): self
    {
        $model = new static();

        $model->setParameters($parameters);

        return $model;
    }

    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }
}
