<?php

namespace Lorinczdev\Modely;

use File;
use Illuminate\Support\Str;
use Lorinczdev\Modely\Models\Model;
use Symfony\Component\Finder\SplFileInfo;

class Modely
{
    protected array $integrations = [];

    public function register(string $name, string $path): void
    {
        $this->integrations[$name] = $config = $this->getConfig($path);

        $this->prepareModels($config);
    }

    protected function getConfig(string $path)
    {
        return require $path . '/config.php';
    }

    protected function prepareModels(array $config): void
    {
        $modelFiles = collect(File::files($config['dir']['models']));

        $modelFiles->each(function (SplFileInfo $modelFile) use ($config) {
            $contents = file_get_contents($modelFile->getRealPath());

            $nsLine = collect(explode("\n", $contents))
                ->filter(fn ($line) => Str::startsWith($line, 'namespace'))
                ->first();

            /**
             * @var class-string<Model> $modelClass
             */
            $modelClass = Str::of($nsLine)
                ->after('namespace ')
                ->trim()
                ->substr(0, -1)
                ->append('\\')
                ->append($modelFile->getBasename('.php'))
                ->toString();

            $modelClass::$config = $config;
        });
    }

    public function getIntegration(string $name): array
    {
        return $this->integrations[$name];
    }

    public function getIntegrations(): array
    {
        return $this->integrations;
    }
}

