<?php

namespace Lorinczdev\Modely\Models\Concerns;

trait HasParameters
{
    protected array $parameters = [];

    protected array $relationParameters = [];

    public function getParameters(): array
    {
        return [
            ...$this->parameters,
            ...$this->relationParameters,
        ];
    }

    public function setRelationParameters(array $parameters): static
    {
        $this->relationParameters = $parameters;

        return $this;
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
