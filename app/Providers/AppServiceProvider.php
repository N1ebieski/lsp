<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Phar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        config([
            'logging.channels.single.path' => $this->getLoggingPath(),
        ]);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Get logging path.
     */
    protected function getLoggingPath(): string
    {
        if (! Phar::running()) {
            return storage_path('logs/lsp.log');
        }

        File::ensureDirectoryExists(
            $dir = dirname(Phar::running(false)) . '/logs'
        );

        return $dir . '/lsp.log';
    }
}
