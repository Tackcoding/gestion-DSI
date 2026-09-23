<?php

use App\Http\Controllers\DemandeAbsencePdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SignalementPdfController;
use App\Http\Middleware\CompteActif;
use App\Models\Evenement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

// Racine : chacun est envoye vers son accueil selon son role.
Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return redirect()->route(
        Gate::allows('voir-tableau-de-bord') ? 'tableau-de-bord' : 'accueil'
    );
});

Route::get('/tableau-de-bord', function () {
    return view('tableau-de-bord');
})->middleware(['auth', CompteActif::class, 'verified', 'can:voir-tableau-de-bord'])->name('tableau-de-bord');

// CompteActif : un compte desactive est deconnecte au prochain chargement de page.
Route::middleware(['auth', CompteActif::class])->group(function () {

    // --- Compte (pas de suppression par l'agent : voir page Agents) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // --- Accueil des roles autres que directeur et administrateur ---
    Route::view('/accueil', 'accueil')->name('accueil');

    // --- Evenements ---
    Route::view('/evenements', 'evenements.index')->name('evenements.index');

    Route::get('/evenements/{evenement}', function (Evenement $evenement) {
        return view('evenements.show', compact('evenement'));
    })->name('evenements.detail');

    // --- Absences ---
    Route::view('/absences', 'absences.index')->name('absences.index');

    Route::view('/absences/validation', 'absences.validation')
        ->middleware('can:valider-absence')
        ->name('absences.validation');

    Route::view('/absences/planning', 'absences.planning')
        ->middleware('can:valider-absence')
        ->name('absences.planning');

    // Tout utilisateur connecte peut generer le formulaire (decision du directeur).
    Route::get('/absences/{demande}/formulaire', DemandeAbsencePdfController::class)
        ->name('absences.formulaire');

    // --- Materiel ---
    Route::view('/materiels', 'materiels.index')
        ->middleware('can:gerer-materiel')
        ->name('materiels.index');

    Route::view('/registre', 'mouvements.index')
        ->middleware('can:gerer-materiel')
        ->name('registre.index');

    Route::view('/signalements', 'signalements.index')
        ->middleware('can:gerer-materiel')
        ->name('signalements.index');

    Route::get('/signalements/{signalement}/pv', SignalementPdfController::class)
        ->middleware('can:gerer-materiel')
        ->name('signalements.pdf');

    // --- Agents (et comptes de connexion) ---
    Route::view('/agents', 'agents.index')
        ->middleware('can:gerer-agents')
        ->name('agents.index');

    // --- Prototype du design system : A RETIRER avant la livraison a la DSI ---
    Route::view('/demo-ui', 'demo-ui');
});

require base_path('routes/auth.php');
