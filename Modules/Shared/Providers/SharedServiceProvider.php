<?php

namespace Modules\Shared\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Shared\Repositories\Interfaces\SharedRepositoryInterface;
use Modules\Shared\Repositories\SharedRepository;

class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SharedRepositoryInterface::class, SharedRepository::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'shared');
        
        Blade::directive('headline', function ($expression) {
            return "<?php echo \Illuminate\Support\Str::headline($expression); ?>";
        });

        /*Blade::directive('activeLink', function ($route) {
            return "<?php echo request()->routeIs($route) ? 'active' : ''; ?>";
        });*/

        Blade::directive('activeLink', function ($expression) {
            return '<?php echo ('.collect(explode(',', $expression))
                ->map(fn ($route) => "request()->routeIs(trim($route))")
                ->implode(' || ').") ? 'active' : ''; ?>";
        });

        Blade::directive('date', function ($date) {
            // return $date->format('M d, Y');
            return "<?php echo \Carbon\Carbon::parse($date)->format('M d, Y'); ?>";
        });
    }
}
