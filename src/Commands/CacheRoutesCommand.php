<?php

namespace Lorinczdev\Modely\Commands;

use Illuminate\Console\Command;
use Lorinczdev\Modely\Routing\ApiRouter;

class CacheRoutesCommand extends Command
{
    protected $signature = 'modely:cache';

    protected $description = 'Cache API routes';

    public function handle(): void
    {
        cache()->forget('modely.routes');

        app(ApiRouter::class)->compileRoutes();

        app(ApiRouter::class)->cacheRoutes();
    }
}
