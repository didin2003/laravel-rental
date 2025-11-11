<?php

namespace Modules\Pricing\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Pricing\Repositories\Interfaces\PricingRepositoryInterface;
use Modules\Pricing\Repositories\PricingRepository;

class PricingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PricingRepositoryInterface::class, PricingRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        // $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'pricing');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
