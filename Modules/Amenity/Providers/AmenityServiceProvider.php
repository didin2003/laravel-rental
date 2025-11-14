<?php

namespace Modules\Amenity\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Amenity\Repositories\AmenityRepository;
use Modules\Amenity\Repositories\Interfaces\AmenityRepositoryInterface;
use Modules\Property\Models\Property;

class AmenityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AmenityRepositoryInterface::class, AmenityRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'amenity');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
