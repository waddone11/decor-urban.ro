<?php

namespace App\Providers;

use App\Models\Category;
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
        // Categoriile pentru meniul/footer-ul storefront-ului (header + footer + layout).
        View::composer(
            ['components.layouts.storefront', 'components.storefront.header', 'components.storefront.footer'],
            function ($view) {
                $view->with('navCategories', Category::query()->active()->ordered()->withCount('products')->get());
            }
        );
    }
}
