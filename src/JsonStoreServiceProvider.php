<?php

namespace FlexibleLabs\JsonStore;

use Illuminate\Support\ServiceProvider;

class JsonStoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // Publish the configuration file when the user runs vendor:publish.
        $this->publishes([
            __DIR__ . '/../config/jsonstore.php' => config_path('jsonstore.php'),
        ], 'jsonstore-config');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Merge the package configuration with the application's configuration.
        $this->mergeConfigFrom(
            __DIR__ . '/../config/jsonstore.php', 'jsonstore'
        );
    }
}
