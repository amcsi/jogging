<?php

namespace App\Providers;

use App\JoggingTime;
use App\Policies\JoggingTimePolicy;
use App\Policies\UserPolicy;
use App\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\DB;
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
        $this->ensurePasswordClientIsNotFirstParty();
    }

    private function ensurePasswordClientIsNotFirstParty(): void
    {
        $clientId = config('services.passport.password_client_id');

        if (! $clientId) {
            return;
        }

        $client = DB::table('oauth_clients')
            ->where('id', $clientId)
            ->first();

        if (! $client || $client->user_id !== null) {
            return;
        }

        $ownerId = DB::table('users')->min('id');

        if ($ownerId === null) {
            return;
        }

        DB::table('oauth_clients')
            ->where('id', $clientId)
            ->update([
                'user_id' => $ownerId,
                'provider' => $client->provider ?? 'users',
            ]);
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
