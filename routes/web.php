<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(
    auth()->check() ? 'tableau-de-bord' : 'login'
));

Route::get('/tableau-de-bord', function () {
    return view('tableau-de-bord');
})->middleware(['auth', 'verified'])->name('tableau-de-bord');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
      Route::view('/registre', 'mouvements.index')
        ->middleware('can:gerer-materiel')
        ->name('registre.index');

    Route::view('/materiels', 'materiels.index')
        ->middleware('can:gerer-materiel')
        ->name('materiels.index');

    Route::view('/agents', 'agents.index')
        ->middleware('can:gerer-agents')
        ->name('agents.index');

    Route::view('/evenements', 'evenements.index')->name('evenements.index');

    Route::get('/evenements/{evenement}', function (App\Models\Evenement $evenement) {
        return view('evenements.show', compact('evenement'));
    })->name('evenements.detail');

    Route::view('/absences/validation', 'absences.validation')
        ->middleware('can:valider-absence')
        ->name('absences.validation');

   Route::view('/absences', 'absences.index')->name('absences.index');

    Route::view('/signalements', 'signalements.index')
        ->middleware('can:gerer-materiel')
        ->name('signalements.index');

    Route::get('/signalements/{signalement}/pv',
        App\Http\Controllers\SignalementPdfController::class)
        ->middleware('can:gerer-materiel')
        ->name('signalements.pdf');

});

require base_path('routes/auth.php');
