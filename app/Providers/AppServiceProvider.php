<?php

namespace App\Providers;

use App\Interfaces\AuthRepositoryInterface;
use App\Models\PersonalAccessToken;
use App\Repositories\AuthRepository;
use App\Services\Global\RateLimiterService;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(abstract: AuthRepositoryInterface::class, concrete: AuthRepository::class);

        $this->app->bind(abstract: RateLimiterService::class, concrete: function ($app) {
            return new RateLimiterService(
                $app->make(RateLimiter::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
