<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Project;
use App\Support\Feeds\ProductFeed;
use App\Support\Sitemap;
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
        foreach ([Category::class, Product::class, ProductImage::class, Project::class] as $model) {
            $model::saved(fn () => $this->forgetCatalogDerivedCaches());
            $model::deleted(fn () => $this->forgetCatalogDerivedCaches());
        }

        // Categoriile pentru meniul/footer-ul storefront-ului (header + footer + layout).
        View::composer(
            ['components.layouts.storefront', 'components.storefront.header', 'components.storefront.footer'],
            function ($view) {
                $view->with('navCategories', Category::query()->active()->ordered()->withCount('products')->get());
            }
        );
    }

    private function forgetCatalogDerivedCaches(): void
    {
        Sitemap::forgetCache();
        ProductFeed::forgetCache();
    }
}
