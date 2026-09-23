<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Un compte desactive pendant qu'il est connecte est deconnecte
 * au prochain chargement de page.
 */
class CompteActif
{
    public function handle(Request $request, Closure $next): Response
    {
        $utilisateur = $request->user();

        if ($utilisateur && ! $utilisateur->actif) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Ce compte a été désactivé. Adressez-vous à l\'administrateur.',
            ]);
        }

        return $next($request);
    }
}
