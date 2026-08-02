<?php

namespace App\Providers;

use App\Models\Information;
use App\Models\Language;
use App\Models\PostCategory;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $languages = Language::getActiveLanguages();
            $postCategories = ProductCategory::getCategories();
            $information = Information::getInformation();

            $view->with('languages', $languages);
            $view->with('postCategories', $postCategories);
            $view->with('information_global', $information);
        });

        if (Request::is('admin/*')) {
            View::share('user', auth()->user());
        }
    }
}
