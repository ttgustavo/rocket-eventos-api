<?php

namespace App\Presenter\Providers;

use App\Domain\Repository\AttendeeRepository;
use App\Domain\Repository\EventRepository;
use App\Domain\Repository\UserRepository;
use App\Domain\Usecase\RegisterUsecase;
use App\Infrastructure\Eloquent\Repository\AttendeeRepositoryEloquent;
use App\Infrastructure\Eloquent\Repository\EventRepositoryEloquent;
use App\Infrastructure\Eloquent\Repository\UserRepositoryEloquent;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->bindRepositories();

        $this->app->bind(RegisterUsecase::class, function(Application $app) {
            return new RegisterUsecase($app->make(UserRepository::class));
        });
    }

    public function boot(): void
    {
    }

    private function bindRepositories(): void
    {
        $this->app->bind(AttendeeRepository::class, AttendeeRepositoryEloquent::class);
        $this->app->bind(EventRepository::class, EventRepositoryEloquent::class);
        $this->app->bind(UserRepository::class, UserRepositoryEloquent::class);
    }
}
