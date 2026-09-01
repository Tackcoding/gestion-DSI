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
        Gate::define('gerer-materiel',       fn ($u) => $u->role->peutGererMateriel());
        Gate::define('gerer-evenements',     fn ($u) => $u->role->peutGererEvenements());
        Gate::define('gerer-agents',         fn ($u) => $u->role->peutGererAgents());
        Gate::define('valider-reservation',  fn ($u) => $u->role->peutValiderReservation());
        Gate::define('valider-absence',      fn ($u) => $u->role->peutValiderAbsence());
        Gate::define('administrer',          fn ($u) => $u->role->estResponsable());
    }
}