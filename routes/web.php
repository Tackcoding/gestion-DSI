<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::view('/materiels', 'materiels.index')->name('materiels.index');
    Route::view('/agents', 'agents.index')->name('agents.index');
        Route::view('/evenements', 'evenements.index')->name('evenements.index');

    Route::get('/evenements/{evenement}', function (App\Models\Evenement $evenement) {
        return view('evenements.show', compact('evenement'));
    })->name('evenements.show');
});

require base_path('routes/auth.php');