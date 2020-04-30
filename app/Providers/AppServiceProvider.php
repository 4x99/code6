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
            define('VERSION', filemtime(base_path('public')));  // 仅编辑文件需 touch 目录
        } else {
            define('VERSION', time());
        }
    }
}
