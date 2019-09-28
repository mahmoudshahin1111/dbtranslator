<?php

namespace LiteMs\Translator;

use Illuminate\Support\ServiceProvider;

class TranslatorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/translation.php' => config_path('translation.php'),
        ]);
        $this->loadMigrationsFrom(__DIR__.'/migration');
        $this->loadRoutesFrom(__DIR__ . '/routes/trans_routes.php');
        $this->loadViewsFrom(__DIR__ . '/view', 'trans');
    }
}
