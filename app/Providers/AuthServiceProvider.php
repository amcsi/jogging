<?php

namespace App\Providers;

use App\JoggingTime;
use App\Policies\JoggingTimePolicy;
use App\Policies\UserPolicy;
use App\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        JoggingTime::class => JoggingTimePolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Passport::enablePasswordGrant();
        Passport::$clientUuids = false;

        $this->ensurePassportKeyPermissions();
    }

    private function ensurePassportKeyPermissions(): void
    {
        foreach ([storage_path('oauth-private.key'), storage_path('oauth-public.key')] as $path) {
            if (is_file($path)) {
                @chmod($path, 0600);
            }
        }
    }
}
