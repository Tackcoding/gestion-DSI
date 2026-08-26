<?php

namespace App\Providers;

use App\Enums\RoleUtilisateur;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Gate::define('valider-reservation', fn ($user) =>
            $user->role->peutValiderReservation());

        Gate::define('valider-absence', fn ($user) =>
            $user->role->peutValiderAbsence());

        Gate::define('gerer-materiel', fn ($user) => in_array($user->role, [
            RoleUtilisateur::Depositaire,
            RoleUtilisateur::Administrateur,
        ], true));

        Gate::define('administrer', fn ($user) =>
            $user->role === RoleUtilisateur::Administrateur);
    }
}