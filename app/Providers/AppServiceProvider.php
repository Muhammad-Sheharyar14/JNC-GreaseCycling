<?php

namespace App\Providers;

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
        \Filament\Support\Facades\FilamentAsset::register([
            \Filament\Support\Assets\Js::make('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . env('VITE_GOOGLE_MAPS_API_KEY') . '&libraries=places'),
        ]);
    }
}
