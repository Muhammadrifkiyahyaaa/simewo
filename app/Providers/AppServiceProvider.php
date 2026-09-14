<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        // Karena project ini memakai Bootstrap (bukan Tailwind) untuk styling,
        // paginasi (tombol "Next"/"Previous") juga dirender dengan style Bootstrap
        // supaya konsisten dan ikon panahnya tidak raksasa tak terkontrol.
        Paginator::useBootstrapFive();
    }
}
