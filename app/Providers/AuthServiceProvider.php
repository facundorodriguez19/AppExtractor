<?php

namespace App\Providers;

use App\Models\Nota;
use App\Policies\NotaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Nota::class => NotaPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
