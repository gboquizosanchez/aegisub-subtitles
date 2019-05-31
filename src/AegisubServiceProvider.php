<?php

namespace Aegisub;

use Illuminate\Support\ServiceProvider;

class AegisubServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Ass::class, function () {
            return new Ass();
        });

        $this->app->alias(Ass::class, 'aegisub');
    }
}
