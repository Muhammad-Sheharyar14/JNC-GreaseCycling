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
        $this->app->bind(
            \Filament\Http\Responses\Auth\Contracts\LogoutResponse::class,
            \App\Http\Responses\LogoutResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Filament\Support\Facades\FilamentAsset::register([
            \Filament\Support\Assets\Js::make('google-maps', 'https://maps.googleapis.com/maps/api/js?key=' . env('VITE_GOOGLE_MAPS_API_KEY') . '&libraries=places'),
        ]);

        \Filament\Support\Facades\FilamentView::registerRenderHook(
            \Filament\View\PanelsRenderHook::HEAD_END,
            fn (): string => '<style>
                @media (max-width: 1024px) {
                    .fi-topbar {
                        height: calc(4rem + env(safe-area-inset-top, 0px)) !important;
                        padding-top: env(safe-area-inset-top, 0px) !important;
                    }
                    .fi-sidebar {
                        padding-top: env(safe-area-inset-top, 0px) !important;
                    }
                }
            </style>',
        );
    }
}
