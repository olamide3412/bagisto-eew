<?php

namespace Webkul\EewTheme\Providers;

use Illuminate\Support\ServiceProvider;

class EewThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
       // dd('EewThemeServiceProvider');
       $this->publishes([
        __DIR__.'/../Resources/views' => resource_path('themes/eew-theme/views'),
       ]);
    }
}
