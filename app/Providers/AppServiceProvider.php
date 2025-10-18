<?php

namespace App\Providers;

use App\Services\SlugService;
use App\View\Components\LanguageSwitcher;
use App\View\Components\website\header;
use App\View\Components\website\layout;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
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
        $this->app->singleton(SlugService::class, function ($app) {
            return new SlugService;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (request()->isSecure()) {
            config(['session.secure' => true]);
        }
        Session::start(); // force session start
        // Register Blade components
        Blade::component('website.layout', layout::class);
        Blade::component('website.header', header::class);
        Blade::component('language-switcher', LanguageSwitcher::class);
        // Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        // Set default string length for migrations
        Schema::defaultStringLength(191);
    }
}
