<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('app.env') !== 'local') {
            error_reporting(0);
            define('VERSION', trim(file_get_contents(base_path('version'))));
        } else {
            error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
            define('VERSION', time());
        }
    }
}
