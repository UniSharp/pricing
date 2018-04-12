<?php

namespace UniSharp\Pricing;

use UniSharp\Pricing\Pricing;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\ServiceProvider;

class PricingServiceProvider extends ServiceProvider
{
    /**
     * Boot the services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootConfig();

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('pricing', function ($app) {
            return new Pricing(
                $app,
                new Pipeline($app),
                $app['config']['pricing.modules']
            );
        });
    }

    /**
     * Boot configure.
     *
     * @return void
     */
    protected function bootConfig()
    {
        $path = __DIR__ . '/config/pricing.php';

        $this->mergeConfigFrom($path, 'pricing');

        if (function_exists('config_path')) {
            $this->publishes([$path => config_path('pricing.php')]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['pricing'];
    }
}