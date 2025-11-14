<?php

namespace Modules\Property\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Property\Repositories\Interfaces\PropertyRepositoryInterface;
use Modules\Property\Repositories\PropertyRepository;
use Modules\Property\Models\Property;
use Illuminate\Support\Facades\Route;

class PropertyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PropertyRepositoryInterface::class, PropertyRepository::class);

    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        // $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'property');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        Route::model('property', Property::class);
        Route::bind('property', function ($value) {
            return Property::where('slug', $value)->sole();
        });
    }
}
