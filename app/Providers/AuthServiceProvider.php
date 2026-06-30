<?php

namespace App\Providers;

use App\JoggingTime;
use App\Policies\JoggingTimePolicy;
use App\Policies\UserPolicy;
use App\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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

}
