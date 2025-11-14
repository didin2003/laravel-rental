<?php

namespace Modules\Nearby\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Nearby\Repositories\Interfaces\NearbyRepositoryInterface;
use Modules\Nearby\Repositories\NearbyRepository;

class NearbyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NearbyRepositoryInterface::class, NearbyRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        // $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'nearby');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
