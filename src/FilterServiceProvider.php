<?php

namespace CamiloManrique\LaravelFilter;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;

class FilterServiceProvider extends ServiceProvider
{
    use Filterable;
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('filter.php'),
        ]);

        Builder::macro(config('filter.macros.filter'), function ($value = null) {
            return Filterable::filter($value, $this);
        });

        Builder::macro(config('filter.macros.filterAndGet'), function ($value = null) {
            return Filterable::filterAndGet($value, $this);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/config.php', 'filter'
        );
    }
}
