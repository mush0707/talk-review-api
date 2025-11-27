<?php

namespace App\Providers;

use App\Models\Proposal;
use App\Services\Auth\AuthService;
use App\Services\Auth\Contracts\AuthServiceContract;
use App\Services\Auth\Providers\LocalAuthProvider;
use App\Services\Proposals\Policies\ProposalPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Proposal::class => ProposalPolicy::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(AuthServiceContract::class, function ($app) {
            return new AuthService([
                $app->make(LocalAuthProvider::class),
            ]);
        });

        $this->app->alias(AuthServiceContract::class, AuthService::class);
    }

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
