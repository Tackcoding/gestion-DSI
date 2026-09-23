<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\CompteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Premiere connexion (ou acces perdu) : l'agent saisit le code remis par
 * le directeur ou l'administrateur, puis choisit son e-mail et son mot de passe.
 */
class ActivationCompteController extends Controller
{
    public function create(): View
    {
        return view('auth.activer');
    }

    public function store(Request $request, CompteService $comptes): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        $request->validate([
            'code'     => ['required', 'string', 'max:20'],
            'email'    => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'code.required'      => 'Saisissez le code d\'accès.',
            'email.required'     => 'L\'adresse e-mail est obligatoire.',
            'email.email'        => 'Cette adresse e-mail n\'est pas valide.',
            'password.required'  => 'Choisissez un mot de passe.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
        ]);

        $code = $comptes->trouverCodeValide($request->input('code'));

        if (! $code) {
            throw ValidationException::withMessages([
                'code' => 'Ce code est invalide, déjà utilisé ou expiré. Demandez-en un nouveau à l\'administrateur.',
            ]);
        }

        // L'e-mail ne doit appartenir a personne d'autre que l'agent lui-meme
        $request->validate([
            'email' => [Rule::unique('users', 'email')->ignore($code->agent->user_id)],
        ], [
            'email.unique' => 'Cette adresse e-mail est déjà utilisée par un autre compte.',
        ]);

        $utilisateur = $comptes->activer($code, $request->input('email'), $request->input('password'));

        Auth::login($utilisateur);
        $request->session()->regenerate();

        // La racine envoie chacun vers son accueil selon son role
        return redirect('/');
    }
}
