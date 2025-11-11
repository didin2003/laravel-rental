<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Auth\Repositories\Interfaces\UserRepositoryInterface;
use Modules\Auth\Repositories\UserRepository;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {

        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'auth');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
