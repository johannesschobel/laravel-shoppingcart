<?php

namespace JohannesSchobel\ShoppingCart;

use Illuminate\Support\ServiceProvider;

class ShoppingCartServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot() {
        $this->publishes([
            __DIR__.'/../../config/shoppingcart.php'   => config_path('shoppingcart.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../migrations/' => base_path('/database/migrations'),
        ], 'migrations');

        // merge the config and stuff
        $this->setupConfig();
    }

    public function register() {
        // register the facade
        $this->app->bind('shoppingcart', \JohannesSchobel\ShoppingCart\Models\ShoppingCart::class);

        // merge the config and stuff
        $this->setupConfig();
    }

    /**
     * Get the Configuration
     */
    private function setupConfig() {
        $this->mergeConfigFrom(realpath(__DIR__ . '/../../config/shoppingcart.php'), 'shoppingcart');
    }
}
