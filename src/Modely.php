<?php

namespace Lorinczdev\Modely;

use Illuminate\Support\Str;
use Lorinczdev\Modely\Models\Model;
use Lorinczdev\Modely\Routing\Router;
use RuntimeException;

class Modely
{
    protected array $integrations = [];

    public function extend(string $name, array $config): void
    {
        $this->integrations[$name] = $config;

        Router::registerRoutes($name, $config['routes']);
    }

    public function getIntegration(string $name)
    {
        return $this->integrations[$name];
    }

    public function getIntegrationName(string $class): string
    {
        if ((new $class) instanceof Model) {
            return Str::of($class)
                ->beforeLast('\\Models')
                ->afterLast('\\')
                ->snake();
        }

        throw new RuntimeException('Provided class must be a model');
    }

    /**
     * @param class-string<Model> $class
     * @return array
     */
    public static function getConfig(string $class): array
    {
        $self = app(static::class);

        $integrationName = $self->getIntegrationName($class);

        return $self->getIntegration($integrationName);
    }
}
