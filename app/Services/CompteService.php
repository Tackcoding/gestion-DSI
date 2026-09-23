<?php

namespace App\Services;

use App\Enums\RoleUtilisateur;
use App\Models\Agent;
use App\Models\CodeActivation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Comptes de connexion.
 *
 * Personne ne choisit le mot de passe d'un autre : le directeur ou
 * l'administrateur genere un code d'acces, l'agent l'utilise pour choisir
 * son e-mail et son mot de passe. Le meme code sert a retrouver l'acces
 * en cas d'oubli, puisque le serveur n'envoie pas d'e-mails.
 */
class CompteService
{
    public const DUREE_VALIDITE_JOURS = 7;

    /** Sans 0/O, 1/I/L : un code dicte au telephone ne doit pas preter a confusion. */
    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    /**
     * Genere un code pour un agent. Annule les codes precedents non utilises.
     *
     * @return array{0: string, 1: Carbon} le code lisible (XXXX-XXXX) et sa date d'expiration
     */
    public function genererCode(Agent $agent, RoleUtilisateur $role, User $auteur): array
    {
        if (! $auteur->role->peutAttribuer($role)) {
            throw new RuntimeException("Vous ne pouvez pas attribuer le rôle {$role->libelle()}.");
        }

        if ($agent->user && ! $this->peutGerer($auteur, $agent->user)) {
            throw new RuntimeException('Vous ne pouvez pas réinitialiser l\'accès de ce compte.');
        }

        if (! $agent->actif) {
            throw new RuntimeException('La fiche de cet agent est inactive : réactivez-la avant de lui donner un accès.');
        }

        $code    = $this->tirerCode();
        $expire  = now()->addDays(self::DUREE_VALIDITE_JOURS);

        DB::transaction(function () use ($agent, $role, $auteur, $code, $expire) {
            $this->annulerCodes($agent);

            CodeActivation::create([
                'agent_id'    => $agent->id,
                'code_hash'   => $this->empreinte($code),
                'role'        => $role,
                'expire_le'   => $expire,
                'cree_par_id' => $auteur->id,
            ]);
        });

        return [substr($code, 0, 4) . '-' . substr($code, 4), $expire];
    }

    /** Supprime les codes non encore utilises d'un agent. */
    public function annulerCodes(Agent $agent): void
    {
        CodeActivation::where('agent_id', $agent->id)->whereNull('utilise_le')->delete();
    }

    /** Retrouve un code saisi, s'il est encore valable. */
    public function trouverCodeValide(string $saisi): ?CodeActivation
    {
        $code = CodeActivation::with('agent.user')
            ->enAttente()
            ->where('code_hash', $this->empreinte($this->normaliser($saisi)))
            ->first();

        // Fiche agent supprimee ou desactivee depuis la generation du code
        return $code && $code->agent && $code->agent->actif ? $code : null;
    }

    /**
     * Cree le compte de l'agent, ou met a jour son e-mail et son mot de passe
     * s'il en a deja un (code de reinitialisation). Le code devient inutilisable.
     */
    public function activer(CodeActivation $code, string $email, string $motDePasse): User
    {
        return DB::transaction(function () use ($code, $email, $motDePasse) {
            $agent = $code->agent;
            $user  = $agent->user ?? new User();

            $user->fill([
                'name'     => $agent->nom_complet,
                'email'    => $email,
                'password' => $motDePasse,   // hache automatiquement (cast 'hashed' du modele User)
                'role'     => $code->role,
                'actif'    => true,
            ])->save();

            if ($agent->user_id !== $user->id) {
                $agent->update(['user_id' => $user->id]);
            }

            $code->update(['utilise_le' => now()]);

            return $user;
        });
    }

    public function changerRole(User $cible, RoleUtilisateur $role, User $auteur): void
    {
        if (! $this->peutGerer($auteur, $cible)) {
            throw new RuntimeException('Vous ne pouvez pas modifier ce compte.');
        }

        if (! $auteur->role->peutAttribuer($role)) {
            throw new RuntimeException("Vous ne pouvez pas attribuer le rôle {$role->libelle()}.");
        }

        $cible->update(['role' => $role]);
    }

    /** Active ou desactive un compte. Renvoie le nouvel etat. */
    public function basculerActivation(User $cible, User $auteur): bool
    {
        if (! $this->peutGerer($auteur, $cible)) {
            throw new RuntimeException('Vous ne pouvez pas modifier ce compte.');
        }

        $cible->update(['actif' => ! $cible->actif]);

        return $cible->actif;
    }

    /**
     * On ne gere jamais son propre compte (pas de desactivation par erreur),
     * ni un compte d'un role qu'on ne peut pas attribuer.
     */
    public function peutGerer(User $auteur, User $cible): bool
    {
        return $auteur->id !== $cible->id && $auteur->role->peutAttribuer($cible->role);
    }

    /** "k7m4 q2xr", "K7M4-Q2XR" et "K7M4Q2XR" designent le meme code. */
    public function normaliser(string $code): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
    }

    private function empreinte(string $code): string
    {
        return hash('sha256', $code);
    }

    private function tirerCode(): string
    {
        $code = '';

        for ($i = 0; $i < 8; $i++) {
            $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return $code;
    }
}
