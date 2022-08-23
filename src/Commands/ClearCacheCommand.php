<?php

namespace Lorinczdev\Modely\Commands;

use Illuminate\Console\Command;

class ClearCacheCommand extends Command
{
    protected $signature = 'modely:clear-cache';

    protected $description = 'Clear routes cache';

    public function handle(): void
    {
        cache()->forget('modely.routes');
    }
}
