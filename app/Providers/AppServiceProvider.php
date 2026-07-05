<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tell Sanctum to store and look up tokens in the tenant connection,
        // so each tenant's personal_access_tokens table is used.
        PersonalAccessToken::resolveRelationUsing('tokenable', function ($tokenModel) {
            return $tokenModel->morphTo();
        });

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        // Set the tenant connection on the PersonalAccessToken model
        PersonalAccessToken::creating(function ($token) {
            $token->setConnection('tenant');
        });
    }
}
